<?php

namespace App\Http\Controllers;

use App\Models\DiagnosticCenter;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DiagnosticCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allDiagnosticCenters = DiagnosticCenter::get();

        return response()->json([
            'all DiagnosticCenters' => $allDiagnosticCenters,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255|alpha',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'required|string|max:13',
            'address' => 'required|string',
            'hospital_id' => 'required|exists:hospitals,id', // Needed for User creation
            'password' => 'required|confirmed', // Needed for User creation
        ]);

        $diagnsotcCenter = DiagnosticCenter::create(
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
                'role' => 'Diagnostic Admin',
                'associated_id' => $diagnsotcCenter->id, // Link to the patient
            ]
        );

        return response()->json([
            'message' => 'Diagnosic and  user created successfully',
            'diagnostic center' => $diagnsotcCenter,
            'user' => $user,

        ]);
        //

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $singleDiagnosticCenter = DiagnosticCenter::findOrFail($id);

        return response()->json([
            'message' => $singleDiagnosticCenter,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $diagnosticCenter = DiagnosticCenter::where('id', $id)->first();

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email|unique:diagnostic_centers,email,'.$diagnosticCenter->email,
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'hospital_id' => 'nullable|exists:hospitals,id',  // // Password is optional on update
        ]);

        // Update the DiagnosticCenter record
        $diagnosticCenter->update([
            'name' => $validated['name'] ?? $diagnosticCenter->name,
            'email' => $validated['email'] ?? $diagnosticCenter->email,
            'address' => $validated['address'] ?? $diagnosticCenter->address,
            'phone_number' => $validated['phone_number'] ?? $diagnosticCenter->phone_number,
            'hospital_id' => $validated['hospital_id'] ?? $diagnosticCenter->hospital_id,
        ]);

        $user = User::where('associated_id', $id)->where('role', 'Diagnostic Admin')->first();

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
                'role' => 'Diagnostic Admin',
                'associated_id' => $diagnosticCenter->id,
            ]);
        }

        return response()->json([
            'message' => 'DiagnosticCenter updated successfully!',
            'DiagnosticCenter' => $diagnosticCenter,
            'user' => $user->email,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        $diagnosticCenter = DiagnosticCenter::where('id', $id)->first();

        $UserToDelete = User::where('role', 'Diagnostic Admin')
            ->where('associated_id', $diagnosticCenter->id)->first();

        if ($UserToDelete) {
            $UserToDelete->delete();

            dump('user deleted');
        } else {
            return 'no corresponding user account to be deleted';
        }

        $diagnosticCenter->delete();

        return response()->json([
            'message' => 'diagnostic record deleted successfully',
            'record deleted for diagnostic center' => $diagnosticCenter,
            'record deleted for user' => $UserToDelete,
        ]);
    }

    public function fetchNotificationsFromDB()
    {

        if (! Auth::check()) {
            return 404;
        }

        // dd(Auth::user());

        $diagnostic = DiagnosticCenter::where('id', Auth::user()->associated_id)->firstOrFail();

        $notifications = Notification::where('notifiable_type', 'App\Models\DiagnosticCenter')
            ->where('notifiable_id', $diagnostic->id)
            ->where('status', 'pending')
            ->get();

        // $notification = $notifications->map(function ($notification) {
        //     return [
        //         'data' => $notification->data
        //     ];
        // });

        // Map notifications to extract the 'message' from each 'data' array
        $notificationMessages = $notifications->map(function ($not) {
            $not->update(['status' => 'checked']);

            return $not->data;
        }); // Remove null values and convert to array

        return response()->json([
            'message' => 'Notifications you haven’t read',
            'notifications' => $notificationMessages,
        ]);
    }
}
