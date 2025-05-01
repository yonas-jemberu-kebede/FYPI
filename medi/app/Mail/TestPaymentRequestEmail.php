<?php

namespace App\Mail;

use App\Models\Payment;
use App\Models\PendingTesting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestPaymentRequestEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $pendingTesting;

    public $payment;

    public function __construct()
    {

        //PendingTesting $pendingTesting, Payment $payment

        // $this->pendingTesting = $pendingTesting;
        // $this->payment = $payment;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Payment Request Email',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.test',
            // with: [
            //     'patientName' => $this->pendingTesting->patient->firstName,
            //     'testDetails' => $this->pendingTesting->test_requests,
            //     'totalAmount' => $this->pendingTesting->total_amount,
            //     'paymentLink' => $this->payment->checkout_url,
            // ]
            );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
