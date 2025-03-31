<?php

namespace App\Http\Controllers;

use App\Events\PrescriptionOrdered;
use App\Models\PendingPrescription;
use App\Models\Pharmacist;
use carbon\Carbon;
use Illuminate\Http\Request;

class PrescriptionController extends Controller
{
    public function sendPrescription(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:patients,id',
            'hospital_id' => 'required|exists:patients,id',
            'pharmacy_id' => 'required|exists:patients,id',
            'test_id' => 'nullable|exists:tests,id',

            'medications' => 'required|array',
            'medications.*.name' => 'required|string',
            'medications.*.dosage' => 'required|string',
            'medications.*.frequency' => 'required|string',
            'medications.*.items' => 'required|unsignedinteger',

            'instructions' => 'nullable|string',
        ]);

        /* saving the time and date requested */
        $now = Carbon::now();
        $currentDay = strtolower($now->dayName);
        $currentTime = $now->toTimeString();
        /* fetching the pharmacist */
        $pharmacist = Pharmacist::where('pharmacy_id', $validated['pharamacy_id'])
            ->where('shift_day', $currentDay)
            ->where('shift_start', '<=', $currentTime)
            ->where('shift_end', '>=', $currentTime)
            ->firstOrFail();

        /* to store the requested data temporarily until it get */

        $pendingPrescription = PendingPrescription::create(
            [
                'patient_id' => $validated['patient_id'],
                'doctor_id' => $validated['doctor_id'],
                'hospital_id' => $validated['hospital_id'],
                'pharmacy_id' => $validated['pharamacy_id'],
                'test_id' => $validated['test_id'],
                'medications' => $validated['medications'],
                'instructions' => $validated['instructions'],
                'pharamacist_id' => $pharmacist->id,
                'status' => $validated['status'],

            ]
        );

        event(new PrescriptionOrdered($pendingPrescription));

    }
}
