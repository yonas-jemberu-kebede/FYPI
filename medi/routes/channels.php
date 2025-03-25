<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('patient.{patient_id}', function ($user, $patient_id) {
    return $user->id === (int) $patient_id;
});
Broadcast::channel('doctor.{doctor_id}', function ($user, $doctor_id) {
    return $user->id === (int) $doctor_id;
});

Broadcast::channel('hospital.{hospital_id}', function ($user, $hospital_id) {
    return $user->id === (int) $hospital_id;
});

Broadcast::channel('labTechnician.{labTechnician_id}', function ($user, $labTechnician_id) {
    return $user->id === (int) $labTechnician_id;
});
