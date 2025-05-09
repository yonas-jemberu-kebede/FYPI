<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\PrescriptionOrdered;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
class OrderedPrescriptionListener
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
    public function handle(PrescriptionOrdered $event): void
    {





        $notification = [
            'type' => 'prescription.payment.requested',
            'notifiable_type' => 'App\Models\Patient',
            'notifiable_id' => $event->pendingPrescription->patient_id,
            'data' => [
                'message' => 'New Test payment  Requested!',
                'prescription_id' => $event->pendingPrescription->id,
                'checkout_url' => $event->payment->checkout_url,
            ]

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
