<?php

namespace App\Listeners;

use App\Events\TestResultReady;
use App\Models\Notification;

class SendTestResultNotification
{
    /**
     * Create the event listener.
     */
    public $test;

    public function __construct(TestResultReady $event)
    {
        $this->test = $event->test;
    }

    /**
     * Handle the event.
     */
    public function handle(TestResultReady $event): void
    {
        Notification::create([
            'type' => 'test completed',
            'notifiable_type' => 'App\Models\Doctor',
            'notifiable_id' => $event->test->doctor_id,
            'data' => [
                'message' => "tes result has arrived {$event->test->test_result}",
            ],
        ]);
        Notification::create([
            'type' => 'test completed',
            'notifiable_type' => 'App\Models\Patient',
            'notifiable_id' => $event->test->patient_id,
            'data' => [
                'message' => "tes result has arrived {$event->test->test_result}",
            ],
        ]);

    }
}
