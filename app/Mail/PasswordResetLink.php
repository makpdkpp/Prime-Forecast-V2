<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetLink extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $resetUrl;

    public function __construct(User $user, string $token)
    {
        $this->user = $user;
        $this->resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ลิงก์รีเซ็ตรหัสผ่าน - Prime Forecast V2',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-link',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
