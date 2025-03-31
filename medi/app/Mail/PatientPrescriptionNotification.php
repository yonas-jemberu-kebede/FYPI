<?php

namespace App\Mail;

use App\Models\PendingPrescription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PatientPrescriptionNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $pendingPrescription;

    public function __construct(PendingPrescription $pendingPrescription)
    {
        $this->pendingPrescription = $pendingPrescription;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Patient Prescription Notification',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'view.name',
            with: [
                'hospital_name' => $this->pendingPrescription->hospital->name,
                'doctor_name' => $this->pendingPrescription->doctor->first_name,
                'patient_name' => $this->pendingPrescription->patient->first_name,
                'medications' => $this->pendingPrescription->medications,
                'instructions' => $this->pendingPrescription->instructions,
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
