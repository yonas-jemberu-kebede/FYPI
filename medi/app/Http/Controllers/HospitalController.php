<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use App\Models\User;
use Illuminate\Http\Request;

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
            'email' => 'required|email|unique:users,email|unique:Hospitals,email,',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',  // Needed for User creation
            'account' => 'required|string',
        ]);

        $hospital = Hospital::create(
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'address' => $validated['address'],
                'phone_number' => $validated['phone_number'],
                'account' => encrypt($validated['account']),
            ]
        );

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
            '',
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        // Fetch the Hospital
        $hospital = Hospital::findOrFail($id);

        // Validate input while ignoring the current Hospital's email
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email|unique:Hospitals,email,{$id}",
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'account' => 'required|string', // Password is optional on update
        ]);

        // Update the Hospital record
        $hospital->update($validated);

        return response()->json([
            'message' => 'Hospital updated successfully!',
            'Hospital' => $hospital,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $hospitalToDelete = Hospital::findOrFail($id)->first();
        $hospitalToDelete->delete();

        return response()->json([
            'message' => 'Hospital record deleted successfully',
            'record deleted for Hospital' => $hospitalToDelete,

        ]);
    }
}
