<?php

namespace App\Listeners;

use App\Events\PrescriptionOrdered;
use App\Mail\PatientPrescriptionNotification;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;

class PatientPrescriptionEmailNotification
{
    /**
     * Create the event listener.
     */
    public $pendingPrescription;

    public function __construct(PrescriptionOrdered $event)
    {
        $this->pendingPrescription = $event->pendingPrescription;

    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        Notification::create(
            [
                'type' => 'notification arrived',
                'notifiable_type' => 'App\Models\Patient',
                'notifiable_id' => $this->pendingPrescription->patient->id,
                'data' => [
                    'hospital_name' => $this->pendingPrescription->hospital->name,

                    'doctor_name' => $this->pendingPrescription->doctor->first_name,
                    'medications' => $this->pendingPrescription->medications,
                    'instructions' => $this->pendingPrescription->instructions,
                ],
            ]
        );
        /* notification for the pharmacist ,if they were not active by the time of broadcasting */
        Notification::create(
            [
                'type' => 'notification arrived',
                'notifiable_type' => 'App\Models\Pharmacist',
                'notifiable_id' => $this->pendingPrescription->pharmacist->id,
                'data' => [
                    'hospital_name' => $this->pendingPrescription->hospital->name,

                    'doctor_name' => $this->pendingPrescription->doctor->first_name,
                    'medications' => $this->pendingPrescription->medications,
                    'instructions' => $this->pendingPrescription->instructions,
                ],
            ]
        );

        Mail::to($this->pendingPrescription->patient->email)->send(new PatientPrescriptionNotification($this->pendingPrescription));

    }
}
