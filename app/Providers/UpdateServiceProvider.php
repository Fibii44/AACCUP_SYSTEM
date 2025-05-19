<?php

namespace App\Providers;

use Codedge\Updater\Events\UpdateAvailable;
use App\Listeners\NotifyAdminsOfNewUpdate;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class UpdateServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        UpdateAvailable::class => [
            NotifyAdminsOfNewUpdate::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
} 