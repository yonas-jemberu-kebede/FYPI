<?php

namespace App\Listeners;

use App\Events\TestRequestConfirmed;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class SendTestToConductNotification
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
    public function handle(TestRequestConfirmed $event): void
    {
        $test = $event->test;

        $notification = [
            'type' => 'appointment.confirmed',
            'notifiable_type' => 'App\Models\LabTechnician',
            'notifiable_id' => $test->labTechnician->id,
            'data' => [
                'message' => 'New Test Requested!',
                'test_id' => $test->id,
                'test_date' => $test->test_date,

            ],
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
