<?php

namespace App\Listeners;

use App\Events\TestResultReady;
use App\Mail\PatientResultNotification;
use App\Models\Doctor;
use App\Models\Notification;
use App\Models\Patient;
use Illuminate\Support\Facades\Mail;

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
    public function handle(object $event): void
    {
        Notification::create([
            'type' => 'test completed',
            'notification_type' => Doctor::class,
            'notification_id' => $this->test->doctor->id,
            'data' => [
                'message' => "tes result has arrived {$this->test->test_result}",
            ],
        ]);
        Notification::create([
            'type' => 'test completed',
            'notification_type' => Patient::class,
            'notification_id' => $this->test->patient->id,
            'data' => [
                'message' => "tes result has arrived {$this->test->test_result}",
            ],
        ]);

        Mail::to($this->test->patient->email)->send(new PatientResultNotification($this->test));
    }
}
