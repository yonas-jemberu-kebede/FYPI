<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PharmacyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allPharmacys = Pharmacy::get();

        return response()->json([
            'all Pharmacys' => $allPharmacys,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|unique:pharmacies,email,',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'hospital_id' => 'required|exists:hospitals,id', // Needed for User creation
            'password' => 'required|confirmed', // Needed for User creation
        ]);

        $pharmacy = Pharmacy::create(
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'address' => $validated['address'],
                'phone_number' => $validated['phone_number'],
                'hospital_id' => $validated['hospital_id'],
            ]
        );
        $user = User::create(
            [
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'Pharmacy Admin',
                'associated_id' => $pharmacy->id, // Link to the patient
            ]
        );

        return response()->json([
            'message' => 'Pharmacy and user created successfully',
            'Pharmacy' => $pharmacy,
            'user' => $user,

        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $singlePharmacy = Pharmacy::findOrFail($id);

        return response()->json([
            'message' => $singlePharmacy,

        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // Fetch the Pharmacy

        $pharmacy = Pharmacy::where('id', $id)->first();

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email|unique:pharmacies,email,'.$pharmacy->email,
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'password' => 'nullable',
            'hospital_id' => 'nullable|exists:hospitals,id',  // // Password is optional on update
        ]);

        // Update the DiagnosticCenter record
        $pharmacy->update([
            'name' => $validated['name'] ?? $pharmacy->name,
            'email' => $validated['email'] ?? $pharmacy->email,
            'address' => $validated['address'] ?? $pharmacy->address,
            'phone_number' => $validated['phone_number'] ?? $pharmacy->phone_number,
            'hospital_id' => $validated['hospital_id'] ?? $pharmacy->hospital_id,
        ]);

        $user = User::where('associated_id', $id)->where('role', 'Pharmacy Admin')->first();

        if ($user) {

            $updateData = [
                'email' => $validated['email'] ?? $user->email,
                'password' => $validated['password'] ?? $user->password,
            ];

            // Only update password if provided
            if (! empty($validated['password'])) {
                $updateData['password'] = bcrypt($validated['password']);
            }

            $user->update([
                'email' => $updateData['email'],
                'password' => $updateData['password'],
                'role' => 'Pharmacy Admin',
                'associated_id' => $pharmacy->id,
            ]);
        }

        return response()->json([
            'message' => 'pharmacy updated successfully!',
            'pharmacy' => $pharmacy,
            'user' => $user->email,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $pharmacy = Pharmacy::where('id', $id)->first();

        $UserToDelete = User::where('role', 'Pharmacy Admin')
            ->where('associated_id', $pharmacy->id)->first();

        dump($UserToDelete);

        if ($UserToDelete) {
            $UserToDelete->delete();
        } else {
            return 'no corresponding user account to be deleted';
        }

        $pharmacy->delete();

        return response()->json([
            'message' => 'pharmacy record deleted successfully',
            'record deleted for pharamcy' => $pharmacy,
            'record deleted for user' => $UserToDelete,
        ]);
    }

    public function fetchNotificationsFromDB()
    {

        if (! Auth::check()) {
            return 404;
        }

        $pharmacy = Pharmacy::where('id', Auth::user()->associated_id)->firstOrFail();

        // dd($doctor);

        $notifications = Notification::where('notifiable_id', $pharmacy->id)
            ->where('notifiable_type', 'App\Models\Pharmacy')
            ->whereNull('status', 'pending')
            ->get();

        // Map notifications to extract the 'message' from each 'data' array
        $notificationMessages = $notifications->map(function ($not) {

            $not->update(['status' => 'pending']);

            return $not->data;
        });

        return response()->json([
            'message' => 'Notifications you haven’t read',
            'notifications' => $notificationMessages,
        ]);
    }
}
