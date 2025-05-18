<?php

namespace App\Listeners;

use App\Events\AppointmentConfirmed;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

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

        Log::info('SendAppointmentConfirmedNotification handling event', [
            'appointment_id' => $event->appointment->id,
        ]);

        // Define notifications to create
        $notifications = [
            [
                'type' => 'appointment.confirmed',
                'notifiable_type' => 'App\Models\Patient',
                'notifiable_id' => $event->appointment->patient_id,
                'data' => [
                    'message' => "Appointment Confirmed! Date: {$event->appointment->appointment_date}, Time: {$event->appointment->appointment_time} With: {$event->appointment->doctor->first_name} at:{$event->appointment->hospital->name}",
                    'appointment_id' => $event->appointment->id,
                ],
                'read_at' => null,
            ],
            [
                'type' => 'appointment.confirmed',
                'notifiable_type' => 'App\Models\Doctor',
                'notifiable_id' => $event->appointment->doctor_id,
                'data' => [
                    'message' => "New Appointment with {$event->appointment->patient->first_name} {$event->appointment->patient->last_name} on {$event->appointment->appointment_date}",
                    'appointment_id' => $event->appointment->id,
                ],
                'read_at' => null,
            ],
            [
                'type' => 'appointment.confirmed',
                'notifiable_type' => 'App\Models\Hospital',
                'notifiable_id' => $event->appointment->hospital_id,
                'data' => [
                    'message' => "New Booking at your hospital with: {$event->appointment->patient->first_name} {$event->appointment->patient->last_name} on {$event->appointment->appointment_date} ",
                    'appointment_id' => $event->appointment->id,
                ],
                'read_at' => null,
            ],
        ];

        // Create notifications with duplicate check
        foreach ($notifications as $notificationData) {
            $exists = Notification::where([
                'type' => $notificationData['type'],
                'notifiable_type' => $notificationData['notifiable_type'],
                'notifiable_id' => $notificationData['notifiable_id'],
                ['data->appointment_id', $notificationData['data']['appointment_id']],
            ])->exists();

            if (! $exists) {
                Notification::create($notificationData);
                Log::info('Notification created', $notificationData);
            } else {
                Log::warning('Duplicate notification skipped', $notificationData);
            }
        }

    }
}
