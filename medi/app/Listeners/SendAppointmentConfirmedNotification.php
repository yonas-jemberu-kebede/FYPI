<?php

namespace App\Listeners;

use App\Events\AppointmentConfirmed;
use App\Models\Notification;

class SendAppointmentConfirmedNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AppointmentConfirmed $event): void
    {
        Notification::create([
            'type' => 'appointment.confirmed',
            'notifiable_type' => 'App\Models\Patient',
            'notifiable_id' => $event->appointment->patient_id,
            'data' => [
                'message' => "Appointment Confirmed! Date: {$event->appointment->appointment_date}, Time: {$event->appointment->appointment_time}",
                'appointment_id' => $event->appointment->id,
            ],
        ]);

        // Doctor notification
        Notification::create([
            'type' => 'appointment.confirmed',
            'notifiable_type' => 'App\Models\Doctor',
            'notifiable_id' => $event->appointment->doctor_id,
            'data' => [
                'message' => "New Appointment with {$event->appointment->patient->first_name}  {$event->appointment->patient->last_name} on {$event->appointment->appointment_date}",
                'appointment_id' => $event->appointment->id,

            ],
        ]);

        // Hospital notification
        Notification::create([
            'type' => 'appointment.confirmed',
            'notifiable_type' => 'App\Models\Hospital',
            'notifiable_id' => $event->appointment->hospital_id,
            'data' => [
                'message' => 'New Booking at your hospital',
                'appointment_id' => $event->appointment->id,
            ],
        ]);
    }
}
