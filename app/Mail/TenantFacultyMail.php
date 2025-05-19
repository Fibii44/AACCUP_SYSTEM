<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TenantFacultyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public $user, public $password)
    {
        Log::info('TenantFacultyMail constructor called', [
            'user_email' => $this->user->email,
            'password_length' => strlen($this->password)
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        Log::info('Building faculty mail envelope');
        return new Envelope(
            subject: 'Welcome to AACCUP - Your Account Credentials',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::info('Building faculty mail content', [
            'template' => 'emails.faculty-welcome',
            'user_email' => $this->user->email
        ]);
        
        return new Content(
            view: 'emails.faculty-welcome',
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

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        Log::info('Building faculty mail message');
        return $this->subject('Welcome to AACCUP - Your Account Credentials')
                    ->view('emails.faculty-welcome');
    }
}