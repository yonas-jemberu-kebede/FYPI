<?php

namespace App\Events;

use App\Models\Payment;
use App\Models\PendingPrescription;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrescriptionOrdered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $pendingPrescription;

    public $payment;

    public function __construct(PendingPrescription $pendingPrescription, Payment $payment)
    {
        $this->pendingPrescription = $pendingPrescription;
        $this->payment = $payment;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('Pharmacist.'.$this->pendingPrescription->patient->id),
        ];
    }

    public function broadcastAs()
    {
        return 'prescription.payment.request';
    }

    public function broadcastWith()
    {
        return [
            'prescription_id' => $this->pendingPrescription->id,
            'patient_id' => $this->pendingPrescription->patient_id,
            'doctor_id' => $this->pendingPrescription->doctor_id,
            'test_id' => $this->pendingPrescription->test_id,
            'medications' => $this->pendingPrescription->medications,
            'instructions' => $this->pendingPrescription->instructions,
            'hospital_id' => $this->pendingPrescription->test->hospital_id,

            'checkout_url' => $this->payment->checkout_url,
        ];
    }
}
