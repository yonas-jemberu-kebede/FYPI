<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DiagnosticCenterController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\HospitalController;
use App\Http\Controllers\LabTechnicianController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PharmacistController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\TestController;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']); // Register using email

Route::put('diagnosticCenters/{id}', [DiagnosticCenterController::class, 'update']);

Route::get('/forgotPasswordAuth', [AuthController::class, 'forgotPasswordAuth']);

Route::get('/otpCheck/{user}', [AuthController::class, 'otpCheck']);

Route::put('/forgotPassword/{user}', [AuthController::class, 'forgotPassword']);

// Register using email
Route::post('/login', [AuthController::class, 'login'])->name('login'); // Login using email
// Route::post('/logout', [AuthController::class, 'logout']); // Logout using email
Route::get('/alluser', [AuthController::class, 'allUser']); // Login using email
Route::get('/hospitalsforappointment', [AppointmentController::class, 'allHospitals']);
Route::get('/doctorsforappointment/{hospital}', [AppointmentController::class, 'getDoctorsInHospital']);

Route::get('/doctorNotification', [DoctorController::class, 'fetchNotificationsFromDB'])->middleware('auth:sanctum');
Route::get('/allDoctors', [DoctorController::class, 'all']);

Route::get('/hospitalNotification', [HospitalController::class, 'fetchNotificationsFromDB'])->middleware('auth:sanctum');

Route::get('/patientNotification', [PatientController::class, 'fetchNotificationsFromDB'])->middleware('auth:sanctum');

Route::get('/diagnosticNotification', [DiagnosticCenterController::class, 'fetchNotificationsFromDB'])->middleware('auth:sanctum');
Route::get('/pharmacyNotification', [PharmacyController::class, 'fetchNotificationsFromDB'])->middleware('auth:sanctum');
Route::get('/showPatient/{patient}', [PatientController::class, 'show']);
Route::put('/updatePatient/{patient}', [PatientController::class, 'update']);

Route::get('/fetchVideoLinkForPatient', [PatientController::class, 'fetchVideoLink'])->middleware('auth:sanctum');
Route::get('/fetchVideoLinkForDoctor', [DoctorController::class, 'fetchVideoLink'])->middleware('auth:sanctum');
Route::get('/patientHistory', [PatientController::class, 'patientHistory'])->middleware('auth:sanctum');

Route::get('/appointments', [AppointmentController::class, 'index'])->middleware('auth:sanctum');
Route::get('/upcomingAppointmentForDoctor', [DoctorController::class, 'upcomingAppointment'])->middleware('auth:sanctum');
Route::get('/upcomingAppointmentForPatient', [PatientController::class, 'upcomingAppointment'])->middleware('auth:sanctum');
Route::post('/cancelAppointment/{appointment}', [AppointmentController::class, 'cancelAppointment'])->middleware('auth:sanctum');

Route::post('/prescriptionCompleted/{prescription}', [PrescriptionController::class, 'prescriptionCompleted']);
Route::get('/specializedDoctors/{specialization}', [DoctorController::class, 'fetchingDoctorsBasedOnSpecialization']);

Route::get('/nearby-hospitals/{latitude}/{longitude}/{radius}', [HospitalController::class, 'getNearbyHospitals']);

Route::get('/forgotPassword', [PatientController::class, 'forgotPassword'])->middleware('auth:sanctum');
Route::put('/videoScheduling/{appointment}', [AppointmentController::class, 'videoChatScheduling'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    //     Route::middleware(['role:Patient'])->group(function () {

    //         Route::resource('patients', PatientController::class);
    //     });
    //     Route::middleware(['role:Hospital Admin'])->group(function () {

    //         Route::resource('doctors', DoctorController::class);
    //         Route::resource('pharmacies', PharmacyController::class);
    //         Route::resource('diagnosticcenters', DiagnosticCenterController::class);
    //         Route::resource('pharmacists', PharmacistController::class);
    //         Route::resource('labtechnicians', LabTechnicianController::class);
    //     });

    //     Route::middleware(['role:Super Admin'])->group(function () {

    //         Route::resource('hospitals', HospitalController::class);
    //     });

});
Route::delete('hospitals/{hospital}', [HospitalController::class, 'destroy']);

Route::put('hospitals/{hospital}', [HospitalController::class, 'update']);

Route::delete('patient/{patient}', [PatientController::class, 'destroy']);
Route::delete('doctor/{doctor}', [DoctorController::class, 'destroy']);

Route::put('/doctors/{doctor}', [DoctorController::class, 'update']);

Route::resource('patients', PatientController::class);
Route::resource('hospitals', HospitalController::class);
Route::resource('doctors', DoctorController::class);
Route::resource('pharmacies', PharmacyController::class);
Route::resource('diagnosticcenters', DiagnosticCenterController::class);

// Route::post('/appointments/book', [AppointmentController::class, 'book']);
Route::get('/appointments/withDoctors', [AppointmentController::class, 'listDoctorsWithThierHospital']);

Route::post('/appointments/pay', [PaymentController::class, 'initiatePayment']);
Route::post('/appointments/book', [AppointmentController::class, 'book'])->middleware('auth:sanctum');

Route::get('/webhook/chapa/{tx_ref}', [PaymentController::class, 'handleChapaWebhook'])->name('payment.return');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/prescription/request', [PrescriptionController::class, 'makeRequest']);

Route::post('/test/request', [TestController::class, 'makeRequest']);
Route::put('/test/result/{test}', [TestController::class, 'completeTest']);

Route::get('/prescription/paymentWebhookHandling/{txRef}', [PrescriptionController::class, 'webhookHandlingForPrescription'])->name('prescription.return');
Route::get('/test/paymentWebhookHandling/{txRef}', [TestController::class, 'webhookHandlingForTesting'])->name('test.return');
