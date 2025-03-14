<?php

namespace App\Listeners;

use App\Events\TestPaymentRequested;
use App\Mail\TestPaymentRequestEmail;
use Illuminate\Support\Facades\Mail;

class SendTestPaymentNotification
{
    /**
     * Create the event listener.
     */
    public function __construct(TestPaymentRequested $event) {}

    /**
     * Handle the event.
     */
    public function handle(TestPaymentRequested $event): void
    {
        $pendingTesting = $event->pendingTesting;
        $patient = $pendingTesting->patient;
        $payment = $pendingTesting->payment;

        Mail::to($patient->email)->send(new TestPaymentRequestEmail($pendingTesting, $payment));
    }
}
