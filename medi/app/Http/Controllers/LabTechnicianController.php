<?php

namespace App\Http\Controllers;

use App\Models\labTechnician;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class labTechnicianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $alllabTechnicians = labTechnician::get();

        return response()->json([
            'all labTechnicians' => $alllabTechnicians,
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
            'email' => 'required|email|unique:users,email|unique:lab_technicians,email',
            'gender' => 'required|in:Male,Female',
            'phone_number' => 'required|string|max:20',
            'diagnostic_center_id' => 'required|exists:diagnostic_centers,id',
            'password' => 'required|string|min:6',
        ]);

        $labTechnician = LabTechnician::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'date_of_birth' => $validated['date_of_birth'],
            'email' => $validated['email'],
            'gender' => $validated['gender'],
            'phone_number' => $validated['phone_number'],
            'diagnostic_center_id' => $validated['diagnostic_center_id'],
        ]);

        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'Lab Technician',
            'associated_id' => $labTechnician->id,
            'gender' => $validated['gender'],
        ]);

        return response()->json([
            'message' => 'Lab Technician and user created successfully',
            'labTechnician' => $labTechnician,
            'user' => $user,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $singlelabTechnician = labTechnician::findOrFail($id);

        return response()->json([
            'message' => $singlelabTechnician,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // Fetch the labTechnician
        $labTechnician = LabTechnician::findOrFail($id);

        // Validate input while ignoring the current labTechnician's email
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',

            'email' => 'required|email|unique:users,email|unique:lab_technicians,email,'.$labTechnician->id,
            'gender' => 'required|in:Male,Female',
            'phone_number' => 'required|string|max:20',
            'diagnostic_center_id' => 'required|exists:diagnostic_centers,id',
            'password' => 'required|string|min:6', // Password is optional on update
        ]);

        // Update the labTechnician record
        $labTechnician->update($validated);

        // Find the corresponding user
        $userToBeUpdated = User::where('associated_id', $id)->where('role', 'Lab Technician')->first();

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
            'message' => 'labTechnician updated successfully!',
            'labTechnician' => $labTechnician,
            'user' => $userToBeUpdated,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the labTechnician record
        $labTechnicianToDelete = LabTechnician::findOrFail($id);

        // Find the corresponding user (if exists)
        $userToDelete = User::where('associated_id', $id)->where('role', 'labTechnician')->first();

        // Delete the user if found
        if ($userToDelete) {
            $userToDelete->delete();
        } else {
            $userToDelete = 'No corresponding user found.';
        }

        // Delete the labTechnician record
        $labTechnicianToDelete->delete();

        return response()->json([
            'message' => 'labTechnician record deleted successfully',
            'labTechnician_deleted' => $labTechnicianToDelete,
            'user_deleted' => $userToDelete,
        ], 200);
    }
}
