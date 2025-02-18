<?php

namespace App\Http\Controllers;

use App\Models\Pharmacist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PharmacistController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allPharmacists = Pharmacist::get();

        return response()->json([
            'all Pharmacists' => $allPharmacists,
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

            'email' => 'required|email|unique:users,email|unique:pharmacists,email',
            'gender' => 'required|in:Male,Female',
            'phone_number' => 'required|string|max:20',
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'password' => 'required|string|min:6', // Needed for User creation
        ]);

        $pharmacist = Pharmacist::create(
            [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'date_of_birth' => $validated['date_of_birth'],
                'email' => $validated['email'],

                'gender' => $validated['gender'],
                'phone_number' => $validated['phone_number'],
                'pharmacy_id' => $validated['pharmacy_id'],
            ]
        );

        $user = User::create(
            [
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'Pharmacist',
                'associated_id' => $pharmacist->id, // Link to the Pharmacist
            ]
        );

        return response()->json([
            'message' => 'Pharmacist and user created successfully',
            'Pharmacist' => $pharmacist,
            'user' => $user,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $singlePharmacist = Pharmacist::findOrFail($id);

        return response()->json([
            'message' => $singlePharmacist,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // Fetch the Pharmacist
        $pharmacist = Pharmacist::findOrFail($id);

        // Validate input while ignoring the current Pharmacist's email
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date',

            'email' => 'required|email|unique:users,email|unique:pharmacists,email,'.$pharmacist->id,
            'gender' => 'required|in:Male,Female',
            'phone_number' => 'required|string|max:20',
            'pharmacy_id' => 'required|exists:pharmacies,id',
            'password' => 'required|string|min:6', // Password is optional on update
        ]);

        // Update the Pharmacist record
        $pharmacist->update($validated);

        // Find the corresponding user
        $userToBeUpdated = User::where('associated_id', $id)->where('role', 'Pharmacist')->first();

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
            'message' => 'Pharmacist updated successfully!',
            'Pharmacist' => $pharmacist,
            'user' => $userToBeUpdated,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Find the Pharmacist record
        $pharmacistToDelete = Pharmacist::findOrFail($id);

        // Find the corresponding user (if exists)
        $userToDelete = User::where('associated_id', $id)->where('role', 'Pharmacist')->first();

        // Delete the user if found
        if ($userToDelete) {
            $userDeleted = $userToDelete->delete();
        } else {
            $userDeleted = 'No corresponding user found.';
        }

        // Delete the Pharmacist record
        $pharmacistToDelete->delete();

        return response()->json([
            'message' => 'Pharmacist record deleted successfully',
            'Pharmacist_deleted' => $pharmacistToDelete,
            'user_deleted' => $userDeleted,
        ], 200);
    }
}
