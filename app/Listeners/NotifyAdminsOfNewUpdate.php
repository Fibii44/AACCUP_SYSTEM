<?php

namespace App\Listeners;

use App\Models\User;
use App\Notifications\SystemUpdateAvailable;
use Codedge\Updater\Events\UpdateAvailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotifyAdminsOfNewUpdate implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(UpdateAvailable $event): void
    {
        // Get version from the event
        $version = $event->getVersionAvailable();
        
        // Find all admin users in the tenant
        $adminUsers = User::where('role', 'admin')->get();
        
        // Notify each admin
        foreach ($adminUsers as $admin) {
            $admin->notify(new SystemUpdateAvailable($version));
        }
    }
} 