<?php

namespace App\Http\Controllers;

use App\Events\TestPaymentRequested;
use App\Events\TestRequestConfirmed;
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
use Illuminate\Support\Facades\Log;

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
        $testIds = $validated['test_id'];
        $totalAmount = TestPrice::whereIn('id', $testIds)->sum('price');

        $now = Carbon::now();
        $currentDay = strtolower($now->dayName);
        $currentTime = $now->toTimeString();

        $labTechnician = LabTechnician::where('hospital_id', $validated['hospital_id'])
            ->where('shift_day', $currentDay)
            ->where('shift_start', '<=', $currentTime)
            ->where('shift_end', '>=', $currentTime)
            ->first();
        $labTechnicianId = $labTechnician ? $labTechnician->id : null;

        $pendingTesting = PendingTesting::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'hospital_id' => $validated['hospital_id'],
            'lab_technician_id' => $labTechnicianId,
            'test_requests' => $testIds,
            'total_amount' => $totalAmount,
        ]);

        $txRef = 'TEST-'.$pendingTesting->id.'-'.time();
        $hospital = Hospital::where('hospital_id', $validated['hospital_id'])->get();
        $chapaSecretKey = $hospital->account;

        $patient = Patient::where('patient_id', $validated['patient_id'])->get();
        $email = $patient->email;

        $chapaResponse = Http::withHeaders([
            'Authorization' => 'Bearer '.$chapaSecretKey,
        ])->post('https://api.chapa.co/v1/transaction/initialize', [
            'amount' => $totalAmount,
            'currency' => 'ETB',
            'email' => $email,
            'tx_ref' => $txRef,
            'callback_url' => 'https://d0c4-149-102-244-114.ngrok-free.app/api/webhook/chapa',
        ]);

        if ($chapaResponse->failed()) {
            return response()->json(['error' => 'Failed to initiate payment'], 500);
        }
        $responseData = $chapaResponse->json(); // converting the comming response from chapa into array

        payment::create([
            'tx_ref' => $txRef,
            'amount' => $totalAmount,
            'currency=>"ETB',
            'status' => 'pending',
            'payable_type' => PendingTesting::class,
            'payable_id' => $pendingTesting->id,
            'checkout_url' => $responseData['data']['checkout_url'],
        ]);

        event(new TestPaymentRequested($pendingTesting));

        return response()->json([
            'checkout_url' => $responseData['data']['checkput_url'],
        ]);
    }

    public function webhookHandlingForTesting(Request $request)
    {
        $payload = $request->getContent();
        Log::info('Chapa Webhook Received', [
            'raw_payload' => $payload,
            'headers' => $request->headers->all(),
        ]);
        // Check for empty payload
        if (empty($payload)) {
            Log::warning('Empty payload received');

            return response()->json(['error' => 'Empty payload'], 400);
        }

        $data = json_decode($payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid JSON', ['payload' => $payload]);

            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        // Extract tx_ref and status from the payload
        $txRef = $data['tx_ref'] ?? null;
        $status = $data['status'] ?? null;

        if (! $txRef || ! $status) {
            Log::error('Missing fields', ['data' => $data]);

            return response()->json(['error' => 'Missing data'], 400);
        }

        // Find the Payment record by tx_ref
        $payment = Payment::where('tx_ref', $txRef)->firstOrFail();
        $payment->update(['status' => $status]);

        if ($status === 'success' && $payment->payable_type === PendingTesting::class) {
            $pendingTesting = $payment->payable;
            $test = Test::create([
                'patient_id' => $pendingTesting->patient_id,
                'doctor_id' => $pendingTesting->doctor_id,
                'hospital_id' => $pendingTesting->hospital_id,
                'lab_technician_id' => $pendingTesting->lab_technician_id,
                'amount' => $pendingTesting->total_amount,
                'status' => $pendingTesting->lab_technician_id ? 'assigned' : 'requested',
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
        }

        return response()->json(['message' => 'Webhook processed'], 200);

    }
}
