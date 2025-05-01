<?php

namespace App\Events;

use App\Models\Payment;
use App\Models\PendingTesting;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TestPaymentRequested
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $pendingTesting;
    public $payment;
    public function __construct(PendingTesting $pendingTesting, Payment $payment)
    {
        $this->pendingTesting = $pendingTesting;
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
            new PrivateChannel('patient.' . $this->pendingTesting->patient->id),
        ];
    }

    public function broadcastWith()
    {
        return [
            'checkout_url' => $this->payment->checkout_url
        ];
    }
}
