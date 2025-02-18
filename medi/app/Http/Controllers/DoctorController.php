<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allDoctors = Doctor::get();

        return response()->json([
            'all Doctors' => $allDoctors,
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
            'specialization' => 'required|string',
            'email' => 'required|email|unique:users,email|unique:Doctors,email',
            'gender' => 'required|in:Male,Female',
            'phone_number' => 'required|string|max:20',
            'hospital_id' => 'required|exists:hospitals,id',
            'password' => 'required|string|min:6', // Needed for User creation
        ]);

        $doctor = Doctor::create(
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'email' => $validated['email'],
                'specialization' => $validated['specialization'],
                'gender' => $validated['gender'],
                'phone_number' => $validated['phone_number'],
                'hospital_id' => $validated['hospital_id'],
            ]
        );

        $user = User::create(
            [
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'Doctor',
                'associated_id' => $doctor->id, // Link to the Doctor
            ]
        );

        return response()->json([
            'message' => 'Doctor and user created successfully',
            'Doctor' => $doctor,
            'user' => $user,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $singleDoctor = Doctor::findOrFail($id);

        return response()->json([
            'message' => $singleDoctor,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // Fetch the Doctor
        $doctor = Doctor::findOrFail($id);

        // Validate input while ignoring the current Doctor's email
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',

            'email' => 'required|email|unique:users,email|unique:Doctors,email,'.$doctor->id,
            'gender' => 'required|in:Male,Female',
            'phone_number' => 'required|string|max:20',
            'hospital_id' => 'required|exists:hospitals,id',
            'password' => 'nullable|string|min:6', // Password is optional on update
        ]);

        // Update the Doctor record
        $doctor->update($validated);

        // Find the corresponding user
        $userToBeUpdated = User::where('associate_id', $id)->where('role', 'Doctor')->first();

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
            'message' => 'Doctor updated successfully!',
            'Doctor' => $doctor,
            'user' => $userToBeUpdated,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the doctor record
        $doctorToDelete = Doctor::findOrFail($id);

        // Find the corresponding user (if exists)
        $userToDelete = User::where('associate_id', $id)->where('role', 'Doctor')->first();

        // Delete the user if found
        if ($userToDelete) {
            $userDeleted = $userToDelete->delete();
        } else {
            $userDeleted = 'No corresponding user found.';
        }

        // Delete the doctor record
        $doctorToDelete->delete();

        return response()->json([
            'message' => 'Doctor record deleted successfully',
            'doctor_deleted' => $doctorToDelete,
            'user_deleted' => $userDeleted,
        ], 200);
    }
}
