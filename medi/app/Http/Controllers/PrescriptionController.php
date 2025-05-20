<?php

namespace App\Http\Controllers;

use App\Events\PrescriptionOrdered;
use App\Events\PrescriptionRequestConfirmed;
use App\Mail\PrescriptionPaymentMail;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\MedicationInventory;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\PendingPrescription;
use App\Models\Pharmacist;
use App\Models\Prescription;
use carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class PrescriptionController extends Controller
{
    public function makeRequest(Request $request)
    {
        // Validate input
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
            'status' => 'nullable|in:pending,confirmed,cancelled', // Add status validation
        ]);

        $medications = $validated['medications'];

        $totalAmount = 0;

        $validMedications = [];

        // Calculate total cost and validate medication stock
        foreach ($medications as $medication) {
            $inventory = MedicationInventory::where('medication_name', $medication['name'])->first();

            if (! $inventory) {
                return response()->json(['error' => "Medication '{$medication['name']}' not found"], 400);
            }

            if ($inventory->quantity_available < $medication['items']) {
                return response()->json(['error' => "Insufficient stock for '{$medication['name']}'"], 400);
            }

            $validMedications[] = $medication;
            $totalAmount += $inventory->price_per_unit * $medication['items'];
        }

        if (empty($validMedications)) {
            return response()->json(['message' => 'No valid medications provided'], 400);
        }

        // Get current time and day
        // $now = Carbon::now();
        // $currentDay = strtolower($now->dayName);
        // $currentTime = $now->toTimeString();

        // dump($currentTime);
        // dump($currentDay);
        // // Find available pharmacist
        // $pharmacist = Pharmacist::where('shift_day', $currentDay)
        //     ->where('shift_start', '<=', $currentTime)
        //     ->where('shift_end', '>=', $currentTime)
        //     ->first();

        // if (! $pharmacist) {
        //     return response()->json(['error' => 'No pharmacist available at this time'], 400);
        // }

        $hospital = Hospital::where('id', $validated['hospital_id'])->firstOrFail();

        $pharmacyId = $hospital->pharmacy->id;

        // dump($pharmacyId);

        // Create pending prescription
        $pendingPrescription = PendingPrescription::create([
            'patient_id' => $validated['patient_id'],
            'doctor_id' => $validated['doctor_id'],
            'hospital_id' => $validated['hospital_id'],
            'test_id' => $validated['test_id'],
            'pharmacy_id' => $pharmacyId,
            'medications' => $validMedications,
            'instructions' => $validated['instructions'],
            'status' => $validated['status'] ?? 'pending',
        ]);

        // If no payment required, return early
        if ($totalAmount <= 0) {
            return response()->json(['message' => 'No payment required'], 200);
        }

        // Initialize payment
        $txRef = 'PRESCRIPTION-'.$pendingPrescription->id.'-'.time();

        $patient = Patient::where('id', $validated['patient_id'])->firstOrFail();

        $chapaResponse = Http::withHeaders([
            'Authorization' => 'Bearer '.$hospital->account,
        ])->post('https://api.chapa.co/v1/transaction/initialize', [
            'amount' => $totalAmount,
            'currency' => 'ETB',
            'email' => $patient->email,
            'tx_ref' => $txRef,
            'return_url' => route('prescription.return', ['txRef' => $txRef]),
        ]);

        if ($chapaResponse->failed() || ! isset($chapaResponse['data']['checkout_url'])) {
            return response()->json(['error' => 'Failed to initiate payment'], 500);
        }

        $responseData = $chapaResponse->json();
        $pendingPrescription->update(['tx_ref' => $txRef]);

        // Store payment details
        $payment = Payment::create([
            'tx_ref' => $txRef,
            'amount' => $totalAmount,
            'currency' => 'ETB',
            'status' => 'pending',
            'payable_type' => PendingPrescription::class,
            'payable_id' => $pendingPrescription->id,
            'checkout_url' => $responseData['data']['checkout_url'],
        ]);

        $patientName = $patient->first_name;

        $checkout_url = $chapaResponse['data']['checkout_url'];

        $doctor = Doctor::where('id', $validated['doctor_id'])->firstOrFail();
        $doctorName = $doctor->first_name;

        $hospitalName = $hospital->name;

        Mail::to($patient->email)->send(new PrescriptionPaymentMail(
            $patientName,
            $doctorName,
            $hospitalName,
            $totalAmount,
            $checkout_url,

        ));

        // Trigger event
        event(new PrescriptionOrdered($pendingPrescription, $payment));

        return response()->json(['checkout_url' => $responseData['data']['checkout_url']], 200);
    }

    public function webhookHandlingForPrescription(Request $request, $txRef)
    {

        $payment = Payment::where('tx_ref', $txRef)->firstOrFail();
        $payment->update(['status' => 'success']);

        $pendingPrescription = PendingPrescription::where('tx_ref', $txRef)->firstOrFail();

        $prescription = Prescription::create([
            'patient_id' => $pendingPrescription->patient_id,
            'doctor_id' => $pendingPrescription->doctor_id,
            'hospital_id' => $pendingPrescription->hospital_id,
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

        return response()->json(['message' => 'Webhook processed'], 200);
    }

    public function prescriptionCompleted(Prescription $prescription)
    {

        $prescription->update([
            'status' => 'completed',
        ]);

        return response()->json([
            'message' => "prescription status changed to 'completed' ",
        ]);
    }
}
