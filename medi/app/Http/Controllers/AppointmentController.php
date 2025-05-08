<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\PendingBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AppointmentController extends Controller
{
    // for  fetching available hospitals in dashboard
    public function allHospitals()
    {
        $allHospitals = Hospital::get();

        return response()->json([
            'allHospitals' => $allHospitals,
        ]);
    }

    // when specific hospital is choosen,hospital id will be captured and doctors who belongs there will be fetched in the page
    public function getDoctorsInHospital(Hospital $hospital)
    {
        $doctors = Doctor::where('hospital_id', $hospital->id)->get();

        return response()->json([
            'doctors' => $doctors,
        ]);
    }

    public function listDoctorsWithThierHospital()
    {
        $doctors = Doctor::with('hospital')->get();

        return response()->json([
            'doctor' => $doctors,
        ]);
    }

    public function book(Request $request)
    {
        // Step 1: Validate the request
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'email' => 'required|email|unique:patients,email',
            'gender' => 'required|in:Male,Female',
            'phone_number' => 'required|string|max:20',
            'password' => 'required|string|min:6',
            'hospital_id' => 'required|exists:hospitals,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
        ]);

        // Step 2: Check doctor availability
        $checkingAvailablility = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])->exists();

        if ($checkingAvailablility) {
            return response()->json(['message' => 'Doctor is not available at this time.'], 400);
        }

        // Step 3: Generate unique transaction reference
        $txRef = 'APPT-'.uniqid();

        // Step 4: Organize temporary data
        $pendingData = [
            'patient' => [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'email' => $validated['email'],
                'gender' => $validated['gender'],
                'phone_number' => $validated['phone_number'],
            ],
            'user' => [
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'Patient',
            ],
            'appointment' => [
                'hospital_id' => $validated['hospital_id'],
                'doctor_id' => $validated['doctor_id'],
                'appointment_date' => $validated['appointment_date'],
                'appointment_time' => $validated['appointment_time'],
                'amount' => 100.00,
            ],
        ];

        // Step 5: Create pending booking
        $pendingBooking = PendingBooking::create([
            'tx_ref' => $txRef,
            'data' => $pendingData,
            'payment_id' => null,
            'hospital_id' => $validated['hospital_id'], // Fixed typo
        ]);

        // Step 6: Return response
        return response()->json([
            'tx_ref' => $txRef,
            'amount' => 100.00,
            'email' => $validated['email'],
            'message' => 'Proceed to payment',
        ], 200);
    }

    public function index(Request $request)
    {

        /**
         * TO BE CHECKED AGAIN
         */
        $appointments = Appointment::where('patient_id', $request->user()->patient->id ?? 0)
            ->orWhere('doctor_id', $request->user()->doctor->id ?? 0)
            ->with(['patient', 'doctor', 'hospital'])
            ->get();

        return response()->json($appointments);
    }
}
