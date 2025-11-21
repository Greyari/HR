<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Event → Listener mapping
     */
    protected $listen = [
        \App\Events\DataResetEvent::class => [
            \App\Listeners\LogResetListener::class,
        ],
    ];

    public function shouldDiscoverEvents()
    {
        return false;
    }

    public function boot(): void
    {
        //
    }
}
