<?php

namespace App\Listeners;

use App\Events\TestPaymentRequested;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class SendTestPaymentNotification
{
    /**
     * Create the event listener.
     */
    public function __construct(TestPaymentRequested $event) {}

    /**
     * Handle the event
     */
    public function handle(TestPaymentRequested $event): void
    {

        $notifications = [

            [
                'type' => 'new.TestRequest',
                'notifiable_type' => 'App\Models\DiagnosticCenter',
                'notifiable_id' => $event->pendingTesting->diagnostic_center_id,
                'data' => [
                    'message' => "New Test Request for {$event->pendingTesting->patient->first_name} {$event->pendingTesting->patient->last_name} from {$event->pendingTesting->doctor->first_name}
                   on {$event->pendingTesting->created_at}",

                ],
                'read_at' => null,
            ],
            [
                'type' => 'new.TestRequest',
                'notifiable_type' => 'App\Models\Patient',
                'notifiable_id' => $event->pendingTesting->patient_id,
                'data' => [
                    'message' => "You have  new Test Request !

                    please finalize your payment through {$event->payment->checkout_url} ",

                ],
                'read_at' => null,
            ],

        ];

        foreach ($notifications as $notification) {

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
}
