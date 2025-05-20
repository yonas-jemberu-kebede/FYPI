<?php

namespace App\Mail;

use App\Models\Test;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestResultMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $test;

    public function __construct(Test $test)
    {
        $this->test = $test;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test Result Mail',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(

            view: 'mail.test_result_mail',
            with: [
                'patient_name' => $this->test->patient->first_name,
                'gender' => $this->test->patient->gender,

                'hospital_name' => $this->test->hospital->name,
                'doctor_name' => $this->test->doctor->first_name,
                'diagnostic_center_name' => $this->test->diagnosticCenter->name,
                'diagnostic_result' => $this->test->test_results,
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
