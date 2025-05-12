<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DoctorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getHospitalDoctors(Hospital $hospital)
    {
        $hospitalDoctors = Doctor::where('hospital_id', $hospital)->get();

        return response()->json([
            'doctors in the hospital' => $hospitalDoctors,
        ]);
    }

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
            'experience' => 'required|integer',
            'specialization' => 'required|string',
            'email' => 'required|email|unique:users,email|unique:Doctors,email',
            'gender' => 'required|in:Male,Female',
            'phone_number' => 'required|string|max:20',
            'hospital_id' => 'required|exists:hospitals,id',

            'image' => 'required|image|mimes:jpg,jpeg,png',

            'password' => 'required|string|min:6', // Needed for User creation
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('doctors', 'public');
        }
        $doctor = Doctor::create(
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'experience' => $validated['experience'],
                'email' => $validated['email'],
                'specialization' => $validated['specialization'],
                'gender' => $validated['gender'],
                'phone_number' => $validated['phone_number'],
                'hospital_id' => $validated['hospital_id'],
                'image' => $imagePath,
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
    public function update(Request $request, Doctor $doctor)
    {

        // Fetch the Doctor

        // Validate input while ignoring the current Doctor's email
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'experience' => 'nullable|integer',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'email' => 'nullable|email|unique:users,email|unique:Doctors,email,'.$doctor,
            'gender' => 'nullable|in:Male,Female',
            'phone_number' => 'nullable|string|max:20',
            'hospital_id' => 'nullable|exists:hospitals,id',
            'password' => 'nullable|string|min:6', // Password is optional on update
        ]);

        //dd($validated);

        $imagePath = $doctor->image; // Keep existing image by default
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            // Store new image
            $imagePath = $request->file('image')->store('doctors', 'public');
        }
        // Update the Doctor record
        $doctor->update([
            'first_name' => $validated['first_name'] ?? $doctor->first_name,
            'last_name' => $validated['last_name'] ?? $doctor->last_name,
            'date_of_birth' => $validated['date_of_birth'] ?? $doctor->date_of_birth,
            'specialization' => $validated['specialization'] ?? $doctor->specialization,
            'email' => $validated['email'] ?? $doctor->email,
            'gender' => $validated['gender'] ?? $doctor->gender,
            'exeprience' => $validated['experience'] ?? $doctor->exeprience,
            'phone_number' => $validated['phone_number'] ?? $doctor->phone_number,
            'hospital_id' => $validated['hospital_id'] ?? $doctor->hospital_id,
            'image' => $imagePath,
        ]);

        // Find the corresponding user
        $userToBeUpdated = User::where('associated_id', $doctor)->where('role', 'Doctor')->first();

        // If user exists, update their email and optionally password
        if ($userToBeUpdated) {
            $updateData = [
                'email' => $validated['email'] ?? $userToBeUpdated->email,
                'gender' => $validated['gender'] ?? $userToBeUpdated->gender,
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

    public function fetchNotificationsFromDB(Doctor $doctor)
    {

        // dd($doctor);

        $notifications = Notification::where('notifiable_id', $doctor->id)
            ->where('notifiable_type', 'App\Models\Doctor')
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
