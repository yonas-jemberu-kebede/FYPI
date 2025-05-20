<?php

namespace App\Listeners;

use App\Events\PrescriptionRequestConfirmed;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class ConfirmedPrescriptionRequest
{
    /**
     * Create the event listener.
     */
    public function __construct() {}

    /**
     * Handle the event.
     */
    public function handle(PrescriptionRequestConfirmed $event): void
    {
        $notification = [
            'type' => 'new.prescritption',
            'notifiable_type' => 'App\Models\Pharmacy',
            'notifiable_id' => $event->prescription->pharmacy_id,
            'data' => [
                'message' => "New prescription for {$event->prescription->patient->first_name} {$event->prescription->patient->last_name} from {$event->prescription->doctor->first_name} on {$event->prescription->created_at}",

            ],
            'read_at' => null,
        ];

        $exists = Notification::where([
            'type' => $notification['type'],
            'notifiable_type' => $notification['notifiable_type'],
            'notifiable_id' => $notification['notifiable_id'],
        ])->exists();

        if (! $exists) {
            Notification::create($notification);
            Log::info('Notification created', $notification);
        } else {
            Log::warning('Duplicate notification skipped', $notification);
        }
    }
}
