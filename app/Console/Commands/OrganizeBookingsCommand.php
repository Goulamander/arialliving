<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Building;
use App\Models\Booking;
use App\Models\BookableItem;


use Illuminate\Console\Command;
use Carbon\Carbon;
use Config;

class OrganizeBookingsCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:organize';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Change status of ended bookings to Archive. (Run in every 15min)';

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

        /**
         * Change status of ended bookings to Archive.
         */

        $now = Carbon::now()->format('Y-m-d H:i:s');

        // Get the bookings that ends now !!
        $bookings = Booking::where('end', '<=', $now)
                           ->whereIn('status', [Booking::$STATUS_ACTIVE, Booking::$STATUS_CONFIRMED])
                           ->pluck('id');

        if( !$bookings ) {
            return;
        }

        // update the status
        Booking::whereIn('id', $bookings)->update(['status' => Booking::$STATUS_COMPLETE]);

    }
}