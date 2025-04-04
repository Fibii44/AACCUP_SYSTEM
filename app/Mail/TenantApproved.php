<?php

namespace App\Mail;

use App\Models\TenantRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TenantApproved extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public TenantRequest $tenantRequest)
    {
        Log::info('TenantApproved mail constructor called', [
            'department' => $this->tenantRequest->department_name,
            'email' => $this->tenantRequest->email,
            'domain' => $this->tenantRequest->domain,
            'has_password' => !empty($this->tenantRequest->password)
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        Log::info('Building mail envelope');
        return new Envelope(
            subject: 'Your Department Registration Has Been Approved',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        Log::info('Building mail content with template data', [
            'template' => 'emails.tenant-approved',
            'password_exists' => !empty($this->tenantRequest->password)
        ]);
        
        return new Content(
            view: 'emails.tenant-approved',
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
        Log::info('Building mail message');
        return $this->subject('Your Department Registration Has Been Approved')
                    ->view('emails.tenant-approved');
    }
}
