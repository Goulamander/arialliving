<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Building;
use App\Models\Booking;
use App\Models\BookableItem;
use App\Models\BookableItem\BookableItemEvent;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Config;

class OrganizeEventsCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:organize';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Change status of the already started events to Archive. (Run in every 15min)';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
        $today = Carbon::today()->format('Y-m-d');
        $time = Carbon::now()->format('H:i:s');

        // Get the bookings that ends now !!
        $events = BookableItemEvent::whereDate('event_date', $today)
                        ->whereTime('event_from', '<=', $time)
                        ->where('bookable_items.status', BookableItem::$STATUS_ACTIVE)
                        ->leftJoin('bookable_items', 'bookable_item_event.bookable_item_id', '=', 'bookable_items.id')
                        ->pluck('bookable_item_id');

        if( !$events ) {
            return;
        }

        // update the status
        BookableItem::whereIn('id', $events)->update(['status' => BookableItem::$STATUS_ARCHIVE]);
    }
}