<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Patient;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allPatients = Patient::get();

        return response()->json([
            'all patients' => $allPatients,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'email' => 'required|email|unique:users,email|unique:patients,email',
            'gender' => 'required|in:Male,Female',
            'phone_number' => 'required|string|max:20',
            'password' => 'required|string|min:6', // Needed for User creation
        ]);

        $patient = Patient::create(
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'email' => $validated['email'],
                'gender' => $validated['gender'],
                'phone_number' => $validated['phone_number'],
            ]
        );

        $user = User::create(
            [
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'Patient',
                'associated_id' => $patient->id, // Link to the patient
            ]
        );

        return response()->json([
            'message' => 'patient and user created successfully',
            'patient' => $patient,
            'user' => $user,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $singlePatient = Patient::findOrFail($id)->first();

        return response()->json([
            'message' => $singlePatient,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // Fetch the patient
        $patient = Patient::findOrFail($id);

        // Validate input while ignoring the current patient's email
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'email' => 'required|email|unique:users,email|unique:patients,email,'.$patient->email,
            'gender' => 'required|in:Male,Female',
            'phone_number' => 'required|string|max:20',
            'password' => 'nullable|string|min:6', // Password is optional on update
        ]);

        // Update the patient record
        $patient->update($validated);

        // Find the corresponding user
        $userToBeUpdated = User::where('associated_id', $id)->where('role', 'Patient')->first();

        // If user exists, update their email and optionally password
        if ($userToBeUpdated) {
            $updateData = [
                'email' => $validated['email'],
                'gender' => $validated['gender'],
            ];

            // Only update password if provided
            if (! empty($validated['password'])) {
                $updateData['password'] = bcrypt($validated['password']);
            }

            $userToBeUpdated->update($updateData);
        }

        return response()->json([
            'message' => 'Patient updated successfully!',
            'patient' => $patient,
            'user' => $userToBeUpdated,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $patientToDelete = Patient::findOrFail($id);
        $UserToDelete = User::where('associate_id', $id)->where('role', 'Patient')->first();

        if ($UserToDelete) {
            $UserToDelete->delete();
        } else {
            $userToDelete = 'no corresponding user account to be deleted';
        }

        $patientToDelete->delete();

        return response()->json([
            'message' => 'patient record deleted successfully',
            'record deleted for patient' => $patientToDelete,
            'record deleted for user' => $UserToDelete,
        ]);
    }

    public function fetchNotificationsFromDB(Patient $patient)
    {

        // dd($doctor);

        $notifications = Notification::where('notifiable_id', $patient->id)
            ->where('notifiable_type', 'App\Models\Patient')
            ->whereNull('read_at')
            ->get();

        // Map notifications to extract the 'message' from each 'data' array
        $notificationMessages = $notifications->pluck('data')->toArray(); // Remove null values and convert to array

        return response()->json([
            'message' => 'Notifications you haven’t read',
            'notifications' => $notificationMessages,
        ]);
    }
}
