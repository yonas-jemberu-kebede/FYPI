<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\PendingBooking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

        if ($doctors->isEmpty()) {
            return response()->json([
                'message' => 'No doctors found for this hospital',
            ], 404);
        }

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

        if (! Auth::user()) {

            return response()->json([
                'message' => 'please sign up first!',
            ]);
        }

        $validated = $request->validate([

            //  'first_name' =>$patient->first_name,
            //  'last_name' =>$patient->last_name,
            //  'email' =>$patient->email,
            //  'date_of_birth' =>$patient->date_of_birth,
            //  'gender' =>$patient->gender,
            //  'phone_number' =>$patient->phone_number,

            // 'last_name' => 'required|string|max:255',
            // 'date_of_birth' => 'required|date',
            // 'email' => 'required|email|unique:patientss,email',
            // 'gender' => 'required|in:Male,Female',
            // 'phone_number' => 'required|string|max:20',
            // 'password' => 'required|string|min:6',

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

        $associateId = User::where('role', 'Patient')
            ->where('id', Auth::user()->id)
            ->firstOrFail();

        $patient = Patient::where('id', $associateId->associated_id)->firstOrFail();

        $txRef = 'APPT-' . uniqid();

        // Step 4: Organize temporary data
        $pendingData = [
            // 'patients' => [
            //     'first_name' => $validated['first_name'],
            //     'last_name' => $validated['last_name'],
            //     'date_of_birth' => $validated['date_of_birth'],
            //     'email' => $validated['email'],
            //     'gender' => $validated['gender'],
            //     'phone_number' => $validated['phone_number'],
            // ],
            // 'user' => [
            //     'email' => $validated['email'],
            //     'password' => Hash::make($validated['password']),
            //     'role' => 'Patients',
            // ],
            'appointment' => [
                'hospital_id' => $validated['hospital_id'],
                'doctor_id' => $validated['doctor_id'],

                'patient_id' => $patient->id,

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
            'email' => $patient->email,
            'message' => 'Proceed to payment',
        ], 200);
    }

    public function index(Request $request)
    {

        if (! Auth::check()) {
            return response()->json([
                'message' => 'not authenticated',
            ]);
        }

        if (Auth::user()->role == 'Doctor') {
            $doctor = Doctor::where('id', Auth::user()->associated_id)->firstOrFail();
            $appointments = Appointment::Where('doctor_id', $doctor->id)->get();
            if ($appointments->isEmpty()) {

                return response()->json([
                    'message' => 'dear doctor ,you have no appointment today',
                ]);
            }

            return response()->json([
                'patient name' => $appointments->patient->first_name,
                'hospital name' => $appointments->hospital->name,
                'appointment date' => $appointments->appointment_date,
                'appointment time' => $appointments->appointment_time,
            ]);
        } elseif (Auth::user()->role == 'Patient') {
            $patient = Patient::where('id', Auth::user()->associated_id)->firstOrFail();

            $appointments = Appointment::Where('patient_id', $patient->id)->get();

            if ($appointments->isEmpty()) {

                return response()->json([
                    'message' => 'dear patient ,you have no appointment today',
                ]);
            }

            $appointmentData = $appointments->map(function ($appointment) {
                return [
                    'doctor name' => $appointment->doctor->first_name,
                    'hospital name' => $appointment->hospital->name,
                    'appointment date' => $appointment->appointment_date,
                    'appointment time' => $appointment->appointment_time,
                ];
            });

            return response()->json(
                $appointmentData
            );
        } elseif (Auth::user()->role == 'Hospital') {
            $hospital = Hospital::where('id', Auth::user()->associated_id)->firstOrFail();
            $appointments = Appointment::Where('hospital_id', $hospital->id)->get();
            if ($appointments->isEmpty()) {

                return response()->json([
                    'message' => 'dear Hospital , no appointment today',
                ]);
            }

            $appointmentData = $appointments->map(function ($appointment) {

                return [
                    'patient name' => $appointment->patient->first_name,
                    'Doctor name' => $appointment->hospital->name,
                    'appointment date' => $appointment->appointment_date,
                    'appointment time' => $appointment->appointment_time,
                ];
            });

            return response()->json($appointmentData);
        }
    }

    public function cancelAppointment(Request $request,Appointment $appointment)
    {
        if (! Auth::check()) {
            return response()->json([
                'message' => 'not authenticated',
            ]);
        }
        $patient = Patient::where('id', Auth::user()->associated_id)->firstOrFail();
        $appointment = Appointment::where('id',$appointment->id)->Where('patient_id', $patient->id)->firstOrFail();

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'appointment cancelled successfully!',
            'cancelled appointment is ' => $appointment,
        ]);
    }
}
