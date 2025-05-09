<?php

namespace App\Http\Controllers;

use App\Events\TestPaymentRequested;
use App\Events\TestRequestConfirmed;
use App\Events\TestResultReady;
use App\Mail\TestPaymentRequestEmail;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\LabTechnician;
use App\Models\patient;
use App\Models\Payment;
use App\Models\PendingTesting;
use App\Models\Test;
use App\Models\TestPrice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class TestController extends Controller
{
    public function makeRequest(Request $request)
    {

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'hospital_id' => 'required|exists:hospitals,id',

            'test_ids' => 'required|array|exists:test_prices,id',
        ]);

        dump($validated);
        $testIds = $validated['test_ids'];
        $totalAmount = TestPrice::whereIn('id', $testIds)->sum('price');

        // $now = Carbon::now();

        // $currentDay = strtolower($now->dayName);
        // $currentTime = $now->toTimeString();
        // // dump($currentDay);
        // // dump($currentTime);
        // $labTechnician = LabTechnician::where('shift_day', $currentDay)
        //     ->where('shift_start', '<=', $currentTime)
        //     ->where('shift_end', '>=', $currentTime)
        //     ->firstOrFail();

        // dump($labTechnician);
        // $labTechnicianId = $labTechnician ? $labTechnician->id : null;


        $hospital=Hospital::where('id',$validated['hospital_id'])->firstOrFail();
        $diagnosticCenterId=$hospital->diagnosticCenter->id;


        $pendingTesting = PendingTesting::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'hospital_id' => $validated['hospital_id'],
            'diagnostic_center_id' => $diagnosticCenterId,
            'test_requests' => $testIds,
            'total_amount' => $totalAmount,
        ]);

        $txRef = 'TEST-'.$pendingTesting->id.'-'.time();


        $chapaSecretKey = $hospital->account;

        $patient = Patient::where('id', $validated['patient_id'])->firstOrFail();
        $email = $patient->email;

        $chapaResponse = Http::withHeaders([
            'Authorization' => 'Bearer '.$chapaSecretKey,
        ])->post('https://api.chapa.co/v1/transaction/initialize', [
            'amount' => $totalAmount,
            'currency' => 'ETB',
            'email' => $email,
            'tx_ref' => $txRef,
            'return_url' => route('test.return', ['txRef' => $txRef]),

        ]);

        if ($chapaResponse->failed()) {
            return response()->json(['error' => 'Failed to initiate payment'], 500);
        }
        $responseData = $chapaResponse->json(); // converting the comming response from chapa into array
        $checkoutUrl = $responseData['data']['checkout_url'];

        $pendingTesting->update(['tx_ref' => $txRef]);

        $payment = payment::create([

            'tx_ref' => $txRef,
            'amount' => $totalAmount,
            'currency' => 'ETB',
            'status' => 'pending',
            'payable_type' => PendingTesting::class,
            'payable_id' => $pendingTesting->id,
            'checkout_url' => $checkoutUrl,

        ]);


        $patientName = $patient->first_name;

        $checkout_url = $chapaResponse['data']['checkout_url'];

        $doctor = Doctor::where('id', $validated['doctor_id'])->firstOrFail();
        $doctorName = $doctor->first_name;

        $hospitalName = $hospital->name;

        Mail::to($patient->email)->send(new TestPaymentRequestEmail(

            $patientName,
            $doctorName,
            $hospitalName,
            $totalAmount,
            $checkout_url,

        ));

        event(new TestPaymentRequested($pendingTesting, $payment));

        return response()->json([
            'checkout_url' => $responseData['data']['checkout_url'],
        ]);
    }

    public function webhookHandlingForTesting(Request $request, $txRef)
    {

        // Find the Payment record by tx_ref
        $payment = Payment::where('tx_ref', $txRef)->firstOrFail();
        $payment->update(['status' => 'success']);
        $pendingTesting = PendingTesting::where('tx_ref', $txRef)->firstOrFail();

        $test = Test::create([
            'patient_id' => $pendingTesting->patient_id,
            'doctor_id' => $pendingTesting->doctor_id,
            'hospital_id' => $pendingTesting->hospital_id,
            'diagnostic_center_id' => $pendingTesting->diagnostic_center_id,
            'total_amount' => $pendingTesting->total_amount,
            'status' => $pendingTesting->diagnostic_center_id ? 'assigned' : 'requested',
            'test_requests' => $pendingTesting->test_requests,
            'test_date' => now()->addDay(),
        ]);
        $payment->update(
            [
                'payable_type' => Test::class,
                'payable_id' => $test->id,
            ]
        );

        $pendingTesting->delete();

        event(new TestRequestConfirmed($test));

        return response()->json(['message' => 'Webhook processed'], 200);
    }

    public function completeTest(Request $request, Test $test)
    {
        // Validate the incoming request data
        $validated = $request->validate([
            'lab_technician_id' => 'required|exists:lab_technicians,id',
            'test_results' => 'required|array',
        ]);

        // Update the Test record with results and status
        $test->update([
            'lab_technician_id' => $validated['lab_technician_id'],
            'test_results' => $validated['test_results'],
            'status' => 'completed',
        ]);

        // Trigger the TestResultReady event
        event(new TestResultReady($test));

        // Return a success response
        return response()->json(['message' => 'Test completed']);
    }
}
