<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemUpdateAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    protected $version;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $version)
    {
        $this->version = $version;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $tenantName = tenant('id') ?? 'your institution';
        
        return (new MailMessage)
            ->subject("System Update Available for $tenantName")
            ->greeting("Hello Admin!")
            ->line("A new system update (version {$this->version}) is available for your AACCUP System.")
            ->line("This update may include security fixes, new features, and improvements.")
            ->action('View Update Details', route('tenant.system-updates.index'))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'System Update Available',
            'message' => "Version {$this->version} is now available",
            'version' => $this->version,
            'link' => route('tenant.system-updates.index'),
        ];
    }
} 