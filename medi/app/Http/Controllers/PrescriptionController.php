<?php

namespace App\Http\Controllers;

use App\Events\PrescriptionOrdered;
use App\Events\PrescriptionRequestConfirmed;
use App\Models\PendingPrescription;
use App\Models\Pharmacist;
use carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\MedicationInventory;
use App\Models\Payment;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Prescription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PrescriptionController extends Controller
{
    public function makeRequest(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'hospital_id' => 'required|exists:hospitals,id',
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'test_id' => 'nullable|exists:tests,id',

            'medications' => 'required|array',
            'medications.*.name' => 'required|string',
            'medications.*.dosage' => 'required|string',
            'medications.*.frequency' => 'required|string',
            'medications.*.items' => 'required|integer|min:0',

            'instructions' => 'nullable|string',

        ]);




        $medications = $validated['medications'];

        $totalAmount = 0;

        foreach ($medications as $medication) {
            // Check if medication exists and has sufficient quantity
            $inventory = MedicationInventory::where('medication_name', $medication['name'])->first();

            // Skip if medication doesn't exist or has insufficient stock
            if (!$inventory || $inventory->quantity_available < $medication['items']) {
                continue;
            }

            // Add to valid medications and calculate cost

            $totalAmount += $inventory->price_per_unit * $medication['items'];
        }

        // Step 3: Check if any valid medications rema

        /* saving the time and date requested */
        $now = Carbon::now();

        $currentDay = strtolower($now->dayName);
        $currentTime = $now->toTimeString();


        /* fetching the pharmacist */
        $pharmacist = Pharmacist::where('shift_day', $currentDay)
            ->where('shift_start', '<=', $currentTime)
            ->where('shift_end', '>=', $currentTime)
            ->firstOrFail();

        /* to store the requested data temporarily until it get */

        $pendingPrescription = PendingPrescription::create(
            [
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'],
                'hospital_id' => $validated['hospital_id'],
                'pharmacy_id' => $validated['pharmacy_id'],
                'test_id' => $validated['test_id'],
                'pharmacist_id' => $pharmacist->id,

                'medications' => $validated['medications'],
                'instructions' => $validated['instructions'],

                'status' => $validated['status'] ?? 'pending',

            ]
        );
        $txRef = 'PRESCRIPTION-' . $pendingPrescription->id . '-' . time();


        $hospital = Hospital::where('id', $validated['hospital_id'])->first();

        $chapaSecretKey = $hospital->account;

        $patient = Patient::where('patient_id', $validated['patient_id'])->get();
        $email = $patient->email;

        if ($totalAmount > 0) {

            $chapaResponse = Http::withHeaders([
                'Authorization' => 'Bearer ' . $chapaSecretKey,
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

            $payment = payment::create([
                'tx_ref' => $txRef,
                'amount' => $totalAmount,
                'currency=>"ETB',
                'status' => 'pending',
                'payable_type' => PendingPrescription::class,
                'payable_id' => $pendingPrescription->id,
                'checkout_url' => $responseData['data']['checkout_url'],
            ]);

            event(new PrescriptionOrdered($pendingPrescription, $payment));
            return response()->json(['checkout_url' => $responseData['data']['checkput_url']], 200);
        }
        return response()->json(['message' => "no payment request"], 200);
    }

    public function webhookHandlingForPrescription(Request $request)
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

        if ($status === 'success' && $payment->payable_type === PendingPrescription::class) {
            $pendingPrescription = $payment->payable;
            $prescription = Prescription::create([
                'patient_id' => $pendingPrescription->patient_id,
                'doctor_id' => $pendingPrescription->doctor_id,
                'hospital_id' => $pendingPrescription->hospital_id,
                'pharmacist_id' => $pendingPrescription->pharmacist_id,
                'pharmacy_id' => $pendingPrescription->pharmacy_id,
                'test_id' => $pendingPrescription->test_id,


                'status' => $pendingPrescription->pharmacist_id ? 'assigned' : 'requested',
                'medications' => $pendingPrescription->medications,
                'instructions' => $pendingPrescription->instructions,


            ]);
            $payment->update(
                [
                    'payable_type' => Prescription::class,
                    'payable_id' => $prescription->id,
                ]
            );

            $pendingPrescription->delete();

            event(new PrescriptionRequestConfirmed($prescription));
        }

        return response()->json(['message' => 'Webhook processed'], 200);
    }
}
