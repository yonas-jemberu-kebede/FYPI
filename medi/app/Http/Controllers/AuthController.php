<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\LabTechnician;
use App\Models\Patient;
use App\Models\Pharmacist;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:Patient,Doctor,Pharmacist,Lab Technician,Hospital Admin,Super Admin',

            // Required fields for specific roles
            'gender' => 'required_if:role,Doctor,Admin,Super Admin,,Lab Technician,Pharmacist|in:Male,Female',
            'date_of_birth' => 'required',
            'specialization' => 'required_if:role,Doctor|string|nullable',
            'hospital_id' => 'required_if:role,Doctor,Hospital Admint|exists:hospitals,id',
            'pharmacy_id' => 'required_if:role,Pharmacist|exists:pharmacies,id',
            'diagnostic_center_id' => 'required_if:role,Lab Technician|exists:diagnostic_centers,id',
        ]);

        $associated_id = null;

        switch ($request->role) {
            case 'Patient':
                $entity = Patient::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,

                    'email' => $request->email,

                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'phone_number' => $request->phone_number,
                ]);
                $associated_id = $entity->id;
                break;

            case 'Doctor':
                $entity = Doctor::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'specialization' => $request->specialization ?? 'General',
                    'email' => $request->email,
                    'hospital_id' => $request->hospital_id,
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'phone_number' => $request->phone_number,
                ]);
                $associated_id = $entity->id;
                break;

            case 'Pharmacist':
                $entity = Pharmacist::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'pharmacy_id' => $request->pharmacy_id,
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'phone_number' => $request->phone_number,
                ]);
                $associated_id = $entity->id;
                break;

            case 'Lab Technician':
                $entity = LabTechnician::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'diagnostic_center_id' => $request->diagnostic_center_id,
                    'date_of_birth' => $request->date_of_birth,
                    'gender' => $request->gender,
                    'phone_number' => $request->phone_number,
                ]);
                $associated_id = $entity->id;
                break;

            case 'Hospital Admin':
                if (Hospital::find($request->hospital_id)) {
                    $associated_id = $request->hospital_id;
                }
                break;

            case 'Super Admin':
                $associated_id = null; // No associated entity for Super Admin
                break;
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'associated_id' => $associated_id,
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'error' => ['Invalid credentials.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function allUser()
    {
        $users = User::all();

        return response()->json([
            'message' => 'all users',
            'all users' => $users,
        ]);
    }
}
