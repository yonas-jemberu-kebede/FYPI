<?php

namespace App\Events;

use App\Models\Test;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestResultReady
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $test;

    public function __construct(Test $test)
    {
        $this->test = $test;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('doctor'.$this->test->doctor->id),
            new PrivateChannel('patient'.$this->test->patient->id),
        ];
    }

    public function broadcastWith()
    {
        return [
            'patient' => "Hello {$this->test->patient->first_name} your test result has arrived {$this->test->test_result}",
            'doctor' => "Hello Dr. {$this->test->doctor->first_name} the test you have requested has arrived {$this->test->test_result}",
        ];
    }

    public function broadcastAs()
    {
        return 'test.result.completed';
    }
}
