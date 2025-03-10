<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentConfirmed implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            // why not $this->appointment->patient_id

            new PrivateChannel('patient.'.$this->appointment->patient->id),
            new PrivateChannel('doctor.'.$this->appointment->doctor->id),
            new PrivateChannel('hospital.'.$this->appointment->hospital->id),
        ];

    }

    public function broadcastAs()
    {
        return 'appointment.confirmed';
    }

    public function broadcastWith()
    {
        return [
            'patient_message' => "Appointment Confirmed! Date: {$this->appointment->appointment_date}, Time: {$this->appointment->appointment_time}",
            'doctor_message' => "New Appointment with {$this->appointment->patient->first_name}  {$this->appointment->patient->last_name} on {$this->appointment->appointment_date}",
            'hospital_message' => 'New Booking at your hospital',
            'appointment_id' => $this->appointment->id,
        ];
    }
}
