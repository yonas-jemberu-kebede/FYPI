<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DiagnosticCenterController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\LabTechnicianController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PharmacistController;
use App\Http\Controllers\PharmacyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']); // Register using email
Route::post('/login', [AuthController::class, 'login']); // Login using email
Route::get('/alluser', [AuthController::class, 'allUser']); // Login using email

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::middleware(['role:Patient'])->group(function () {

        Route::resource('patients', PatientController::class);
    });
    Route::middleware(['role:Hospital Admin'])->group(function () {

        Route::resource('doctors', DoctorController::class);
          Route::resource('pharmacies', PharmacyController::class);
        Route::resource('diagnosticcenters', DiagnosticCenterController::class);
    });

    Route::middleware(['role:Super Admin'])->group(function () {

        Route::resource('hospitals', HospitalController::class);
    });

    // Route::resource('patients', PatientController::class);
    // Route::resource('hospitals', HospitalController::class);
    // Route::resource('doctors', DoctorController::class);
    // Route::resource('pharmacies', PharmacyController::class);
    // Route::resource('diagnosticcenters', DiagnosticCenterController::class);
    Route::resource('pharmacists', PharmacistController::class);
    Route::resource('labtechnicians', LabTechnicianController::class);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
