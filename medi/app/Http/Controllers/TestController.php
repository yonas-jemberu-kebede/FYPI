<?php

namespace App\Http\Controllers;

use App\Events\TestPaymentRequested;
use App\Models\Hospital;
use App\Models\LabTechnician;
use App\Models\patient;
use App\Models\Payment;
use App\Models\PendingTesing;
use App\Models\TestPrice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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

        $pendingTesting = PendingTesing::create([
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
            'payable_type' => PendingTesing::class,
            'payable_id' => $pendingTesting->id,
            'checkout_url' => $responseData['data']['checkout_url'],
        ]);

        event(new TestPaymentRequested($pendingTesting));

        return response()->json([
            'checkout_url' => $responseData['data']['checkput_url'],
        ]);
    }
}
