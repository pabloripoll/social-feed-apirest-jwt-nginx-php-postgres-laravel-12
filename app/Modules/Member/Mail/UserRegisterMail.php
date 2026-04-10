<?php

namespace App\Modules\Member\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserRegisterMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $payload;

    /**
     * Create a new message instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $subject = $this->payload['subject'] ?? 'Welcome to '.config('app.name');

        return $this->subject($subject)
            ->view('emails.member.registration')
            ->text('emails.member.registration_plain');
    }
}
