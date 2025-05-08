<?php

namespace App\Http\Controllers;

use App\Mail\HospitalMail;
use App\Models\Hospital;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;

class HospitalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allHospitals = Hospital::get();

        return response()->json([
            'all Hospitals' => $allHospitals,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|unique:hospitals,email',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'account' => 'required|string',
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'password' => 'required|string|min:8', //confirmed

            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
                'regex:/^-?\d{1,6}(\.\d{1,2})?$/', // Allows up to 6 digits before and 2 after decimal
            ],
            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
                'regex:/^-?\d{1,6}(\.\d{1,2})?$/',
            ],
            'hospital_type' => 'nullable|string',
            'icu_capacity' => 'nullable|integer',
            'established_year' => 'nullable|integer',
            'operating_hours' => 'nullable|string'
        ]);


        dump($validated);
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('hospitals', 'public');
        }
        $hospital = Hospital::create(
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'address' => $validated['address'],
                'phone_number' => $validated['phone_number'],

                'city' => $validated['city'] ?? null,
                'country' => $validated['country'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'hospital_type' => $validated['hospital_type'] ?? null,
                'established_year' => $validated['established_year'] ?? null,
                'operating_hours' => $validated['operating_hours'] ?? null,
                'icu_capacity' => $validated['icu_capacity'] ?? null,

                'account' => encrypt($validated['account']),
                'image' => $imagePath,
            ]
        );

        dump($hospital);

        $user = User::create(
            [
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'Hospital Admin',
                'associated_id' => $hospital->id, // Link to the patient
            ]
        );
        dump($user);

        Mail::to($hospital->email)->send(new HospitalMail($hospital, $validated['password']));

        return response()->json([
            'message' => 'Hospital and user created successfully',
            'Hospital' => $hospital,

        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $singleHospital = Hospital::findOrFail($id);

        return response()->json([
            'message' => $singleHospital,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Hospital $hospital)
    {

        // Fetch the Hospital

        // Validate input while ignoring the current Hospital's email
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email|unique:hospitals,email,{$hospital->id}",
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'account' => 'required|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
            'longitude' => 'nullable|numeric|between:-90,90',
            'latitude' => 'nullable|numeric|between:-180,180',
            'hospital_type' => 'nullable|string',
            'icu_capacity' => 'nullable|integer',
            'established_year' => 'nullable|integer',
            'operating_hours' => 'nullable|integer',
        ]);

        try {
            $hospital->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'address' => $validated['address'],
                'phone_number' => $validated['phone_number'],
                'city' => $validated['city'] ?? null,
                'country' => $validated['country'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'latitude' => $validated['latitude'] ?? null,
                'hospital_type' => $validated['hospital_type'] ?? null,
                'established_year' => $validated['established_year'] ?? null,
                'operating_hours' => $validated['operating_hours'] ?? null,
                'icu_capacity' => $validated['icu_capacity'] ?? null,
                'account' => bcrypt($validated['account']), // or encrypt() if needed
            ]);

            return response()->json([
                'message' => 'Hospital updated successfully!',
                'hospital' => $hospital,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Hospital $hospital)
    {

        $hospital->delete();

        return response()->json([
            'message' => 'Hospital record deleted successfully',
            'record deleted for Hospital' => $hospital,

        ]);
    }

    public function fetchNotificationsFromDB(Hospital $hospital)
    {

        // dd($doctor);

        $notifications = Notification::where('notifiable_id', $hospital->id)
            ->where('notifiable_type', 'App\Models\Hospital')
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
