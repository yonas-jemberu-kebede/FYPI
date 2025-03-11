<?php

namespace App\Http\Controllers;

use App\Events\TestPaymentRequested;
use App\Models\Hospital;
use App\Models\patient;
use App\Models\TestPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TestController extends Controller
{
    public function makeRequest(Request $request)
    {

        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'lab_technician_id' => 'required|exists:lab_technicians,id',
            'diagnostic_center_id' => 'required|exists:diagnostic_centers,id',
            'test_requests' => 'required|array',
        ]);

        $total = 0;
        for ($i = 0; $i < (int) $validated['test_requests']; $i++) {

            $record = TestPrice::Where('test_name', $validated['test_requests'][$i])->get();
            if (! $record) {
                return 'requested test is not available';
            }

            $amount = $record->test_price;
            $total += $amount;
        }

        $validated['amount'] = $total;

        $txRef = 'TEST-'.uniqid();
        $hospital = Hospital::where('hospital_id', $validated['hospital_id'])->get();
        $chapaSecretKey = $hospital->account;

        $patient = Patient::where('patient_id', $validated['patient_id'])->get();
        $email = $patient->email;

        $chapaResponse = Http::withHeaders([
            'Authorization' => 'Bearer '.$chapaSecretKey,
        ])->post('https://api.chapa.co/v1/transaction/initialize', [
            'amount' => $total,
            'currency' => 'ETB',
            'email' => $email,
            'tx_ref' => $txRef,
            'callback_url' => 'https://d0c4-149-102-244-114.ngrok-free.app/api/webhook/chapa',
        ]);

        if ($chapaResponse->failed()) {
            return response()->json(['error' => 'Failed to initiate payment'], 500);
        }
        $responseData = $chapaResponse->json(); // converting the comming response from chapa into array

        // event(new TestPaymentRequested($responseData));

    }
}
