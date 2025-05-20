<?php

namespace App\Http\Controllers;

use App\Mail\ForgotPassword;
use App\Models\DiagnosticCenter;
use App\Models\Doctor;
use App\Models\Hospital;
use App\Models\Patient;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $baseValidation = [

            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'required|string|max:13',
            'role' => 'nullable|in:Patient,Doctor,Hospital Admin,Super Admin,Diagnostic Admin,Pharmacy Admin',

        ];

        $roleValidations = [
            // 'Doctor' => [
            //     'first_name' => 'required|string|max:255',
            //     'last_name' => 'required|string|max:255',
            //     'gender' => 'required|in:Male,Female,Other',
            //     'date_of_birth' => 'required|date|before:-18 years',

            //     'specialization' => 'required|string',
            //     'hospital_id' => 'required|exists:hospitals,id',
            // ],

            'Patient' => [
                'first_name' => 'required|string|max:255|alpha',
                'last_name' => 'required|string|max:255|alpha',
                'gender' => 'required|in:Male,Female',
                'date_of_birth' => 'required|date|before:-18 years',
            ],
            // 'Pharmacy Admin' => [
            //     'name' => 'required|string',
            //     'address' => 'required|string',
            //     'hospital_id' => 'required|exists:hospitals,id',
            // ],
            // 'Diagnostic Admin' => [
            //     'name'=>'required|string',
            //     'address' => 'required|string',
            //     'hospital_id' => 'required|exists:hospitals,id',
            // ],
            // 'Hospital Admin' => [
            //     'hospital_id' => 'required|exists:hospitals,id',
            // ],
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
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                ]);
                $associatedId = $entity->id;
                break;

            case 'Doctor':
                $entity = Doctor::create([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone'],
                    'date_of_birth' => $validated['date_of_birth'],
                    'gender' => $validated['gender'],
                    'specialization' => $validated['specialization'],
                    'hospital_id' => $validated['hospital_id'],
                ]);
                $associatedId = $entity->id;
                break;

            case 'Pharmacy Admin':
                $entity = Pharmacy::create([
                    'name' => $validated['name'],
                    'address' => $validated['address'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone'],
                    'hospital_id' => $validated['hospital_id'],
                ]);
                $associatedId = $entity->id;
                break;

            case 'Diagnostic Admin':
                $entity = DiagnosticCenter::create([
                    'name' => $validated['name'],
                    'address' => $validated['address'],
                    'email' => $validated['email'],
                    'phone_number' => $validated['phone'],
                    'hospital_id' => $validated['hospital_id'],
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

        // dump($request->all());

        // dump($user->password);
        // dump($user->email);

        // if ($user->role == 'Doctor') {
        //     $token = $user->createToken('auth_token')->plainTextToken;

        //     return response()->json([
        //         'message' => 'Login successful',
        //         'token' => $token,
        //         'user' => $user,
        //     ]);
        // }

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
        $user = $request->user();

        dump($user);

        $user->tokens()->delete();

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

    public function forgotPasswordAuth(Request $request)
    {

        $validated = $request->validate([

            'email' => 'required|email|exists:users',

        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user) {
            return response()->json([
                'message' => 'you are not allowed to make change',

            ]);
        }
        $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);

        $user->update([
            'otp' => Hash::make($otp),
        ]);

        Mail::to($user->email)->send(new ForgotPassword(
            $user,
            $otp

        ));

        return response()->json([
            'message' => 'you are ready to go for password update',
            'user_id' => $user->id,
        ]);
    }

    public function otpCheck(Request $request, User $user)
    {
        // Validate the OTP input
        $request->validate([
            'otp' => 'required|string|digits:6',
        ]);

        // Check if user exists and has an OTP
        if (! $user || empty($user->otp)) {
            return response()->json([
                'message' => 'No OTP found for this user',
            ], 422);
        }

        // Verify the OTP
        if (Hash::check($request->otp, $user->otp)) {
            // Optionally clear the OTP after successful verification
            $user->otp = null;
            $user->save();

            return response()->json([
                'message' => 'You are allowed to the next step',
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid OTP',
        ], 422);
    }

    public function forgotPassword(Request $request, User $user)
    {

        $validated = $request->validate([
            'password' => 'required|confirmed',
        ]);
        $user->update(
            [
                'password' => $validated['password'],
            ]

        );

        return response()->json([
            'message' => 'password updated succesfully',
        ]);
    }
}
