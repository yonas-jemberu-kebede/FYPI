<?php

namespace App\Http\Controllers;

use App\Models\DiagnosticCenter;
use Illuminate\Http\Request;

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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|unique:diagnostic_centers,email,',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'hospital_id' => 'required|exists:hospitals,id', // Needed for User creation
        ]);

        $diagnosticCenter = DiagnosticCenter::create(
            [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'address' => $validated['address'],
                'phone_number' => $validated['phone_number'],
                'hospital_id' => $validated['hospital_id'],
            ]
        );

        return response()->json([
            'message' => 'DiagnosticCenter and user created successfully',
            'DiagnosticCenter' => $diagnosticCenter,

        ]);
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

        // Fetch the DiagnosticCenter
        $diagnosticCenter = DiagnosticCenter::findOrFail($id);

        // Validate input while ignoring the current DiagnosticCenter's email
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|unique:diagnostic_centers,email,',
            'phone_number' => 'required|string|max:20',
            'address' => 'required|string',
            'hospital_id' => 'required|exists:hospitals,id',  // // Password is optional on update
        ]);

        // Update the DiagnosticCenter record
        $diagnosticCenter->update($validated);

        return response()->json([
            'message' => 'DiagnosticCenter updated successfully!',
            'DiagnosticCenter' => $diagnosticCenter,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $diagnosticCenterToDelete = DiagnosticCenter::findOrFail($id);
        $diagnosticCenterToDelete->delete();

        return response()->json([
            'message' => 'DiagnosticCenter record deleted successfully',
            'record deleted for DiagnosticCenter' => $diagnosticCenterToDelete,

        ]);
    }
}
