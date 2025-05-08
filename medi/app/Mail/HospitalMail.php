<?php

namespace App\Mail;

use App\Models\Hospital;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HospitalMail extends Mailable
{
    use Queueable, SerializesModels;

    public $hospital;
    public $password;
    /**
     * Create a new message instance.
     */

    public function __construct(Hospital $hospital,string $password)
    {
        $this->hospital = $hospital;
        $this->password = $password;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to Our Platform - Your Hospital Account Details',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.hospital_welcome',
            with: [
                'hospital_name' => $this->hospital->name,
                'hospital_email' => $this->hospital->email,
                'password' => $this->password
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
