<?php

namespace App\Events;

use App\Models\Test;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestRequestConfirmed implements ShouldBroadcast
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
            new PrivateChannel('labTechnician'.$this->test->labTechnician->id),
        ];
    }

    public function broadcastAs()
    {
        return 'New.TestRequest.HasArrived';
    }

    public function broadcastWith()
    {
        return [
            'labTechncianMessage' => "Test Request for {$this->test->patient->first_name} from {$this->test->doctor->first_name}",
        ];
    }
}
