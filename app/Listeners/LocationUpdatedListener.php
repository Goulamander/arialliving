<?php

namespace App\Listeners;

use App\Events\LocationUpdated;
use Illuminate\Contracts\Queue\ShouldQueue;


class LocationUpdatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param LocationUpdated $event
     * @return void
     */
    public function handle(LocationUpdated $event)
    {
        // Access the Location using $event->location...
    }
}
