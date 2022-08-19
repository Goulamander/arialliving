<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

        'App\Events\LocationUpdated' => [
            'App\Listeners\LocationUpdatedListener',
        ],

        // 'App\Events\EventInvoice' => [
        //     'App\Listeners\EventInvoiceListener',
        // ],
        // 'App\Events\EventNotification' => [
        //     'App\Listeners\EventNotificationListener',
        // ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::listen('event.*', function (array $data) {
            //
        });
    }
}
