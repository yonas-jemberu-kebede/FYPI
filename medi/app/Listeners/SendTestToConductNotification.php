<?php

namespace App\Listeners;

use App\Events\TestRequestConfirmed;
use App\Models\Notification;

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

        Notification::create([
            'type' => 'appointment.confirmed',
            'notifiable_type' => 'App\Models\LabTechnician',
            'notifiable_id' => $test->labTechnician->id,
            'data' => [
                'message' => 'New Test Requested!',
                'test_id' => $test->id,
                'test_date' => $test->test_date,

            ],
        ]);
    }
}
