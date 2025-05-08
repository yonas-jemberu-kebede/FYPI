<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PrescriptionPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $patientName,$doctorName,$hospitalName,$totalAmount,$checkout_url;
    public function __construct(string $patientName, string $doctorName, string $hospitalName, string $totalAmount, string $checkout_url)
    {
        $this->patientName=$patientName;
        $this->hospitalName=$hospitalName;
        $this->doctorName=$doctorName;
        $this->totalAmount=$totalAmount;
        $this->checkout_url=$checkout_url;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Prescription Payment Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.prescription_payment_request',
            with:[
                'patient_name'=>$this->patientName,
                'doctor_name'=>$this->doctorName,
                'hospital_name'=>$this->hospitalName,
                'total_amount'=>$this->totalAmount,
                'checkout_url'=>$this->checkout_url,
            ]
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
