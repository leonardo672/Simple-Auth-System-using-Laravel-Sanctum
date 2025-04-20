<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The verification code to send.
     *
     * @var string
     */
    public $code;

    /**
     * Create a new message instance.
     *
     * @param string $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('Your Verification Code')
                    ->view('emails.verification-code')  
                    ->with(['code' => $this->code]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verification Code Mail',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
