<?php

namespace App\Events;

use App\Models\Prescription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrescriptionRequestConfirmed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $prescription;

    public function __construct(Prescription $prescription)
    {
        $this->prescription = $prescription;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('pharmacist'.$this->prescription->pharmacy->id),
        ];
    }

    public function broadcastAs()
    {
        return 'New.PrescriptionRequest.HasArrived';
    }

    public function broadcastWith()
    {
        return [
            'pharmacistMessage' => "Prescription Request for {$this->prescription->patient->first_name} from {$this->prescription->doctor->first_name} and requested medications are {$this->prescription->medications}",
        ];
    }
}
