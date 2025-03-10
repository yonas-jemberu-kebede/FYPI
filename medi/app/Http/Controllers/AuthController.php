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
       
        $baseValidation = [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'required|string|max:20',
            'gender' => 'required|in:Male,Female,Other',
            'dob' => 'required|date|before:-18 years',
            'role' => 'nullable|in:Patient,Doctor,Pharmacist,Lab Technician,Hospital Admin,Super Admin',
        ];
    
      
        $roleValidations = [
            'Doctor' => [
                'specialization' => 'required|string',
                'hospital_id' => 'required|exists:hospitals,id'
            ],
            'Pharmacist' => [
                'pharmacy_id' => 'required|exists:pharmacies,id'
            ],
            'Lab Technician' => [
                'diagnostic_center_id' => 'required|exists:diagnostic_centers,id'
            ],
            'Hospital Admin' => [
                'hospital_id' => 'required|exists:hospitals,id'
            ]
        ];

        $role = $request->input('role', 'Patient');
    
     
        $validated = $request->validate(array_merge(
            $baseValidation,
            $roleValidations[$role] ?? []
        ));
    
        
        $associatedId = null;
        
        switch ($role) {
            case 'Patient':
                $entity = Patient::create([
                    'first_name' => $validated['firstName'],
                    'last_name' => $validated['lastName'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone'],
                    'date_of_birth' => $validated['dob'],
                    'gender' => $validated['gender'],
                ]);
                $associatedId = $entity->id;
                break;
    
            case 'Doctor':
                $entity = Doctor::create([
                    'first_name' => $validated['firstName'],
                    'last_name' => $validated['lastName'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone'],
                    'date_of_birth' => $validated['dob'],
                    'gender' => $validated['gender'],
                    'specialization' => $validated['specialization'],
                    'hospital_id' => $validated['hospital_id'],
                ]);
                $associatedId = $entity->id;
                break;
    
            case 'Pharmacist':
                $entity = Pharmacist::create([
                    'first_name' => $validated['firstName'],
                    'last_name' => $validated['lastName'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone'],
                    'date_of_birth' => $validated['dob'],
                    'gender' => $validated['gender'],
                    'pharmacy_id' => $validated['pharmacy_id'],
                ]);
                $associatedId = $entity->id;
                break;
    
            case 'Lab Technician':
                $entity = LabTechnician::create([
                    'first_name' => $validated['firstName'],
                    'last_name' => $validated['lastName'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone'],
                    'date_of_birth' => $validated['dob'],
                    'gender' => $validated['gender'],
                    'diagnostic_center_id' => $validated['diagnostic_center_id'],
                ]);
                $associatedId = $entity->id;
                break;
    
            case 'Hospital Admin':
               
                if (Hospital::find($validated['hospital_id'])) {
                    $associatedId = $validated['hospital_id'];
                }
                break;
    
            case 'Super Admin':
            
                break;
        }
    
       
        $user = User::create([
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $role,
            'associated_id' => $associatedId,
        ]);
    
    
        $token = $user->createToken('auth_token')->plainTextToken;
    
        return response()->json([
            'message' => 'Registration successful',
            'user' => $user,
            'token' => $token,
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