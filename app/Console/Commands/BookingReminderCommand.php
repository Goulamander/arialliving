<?php

namespace App\Console\Commands;

// use App\Models\User;
// use App\Models\Building;
use App\Models\Booking;
//use App\Models\BookableItem;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Config;

class BookingReminderCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Send out the booking reminders for bookings that due tomorrow. (Run once a day @9am)';

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
        // 1 day in advance
        $tomorrow = Carbon::today()->addDay()->format('Y-m-d');

        $bookings = Booking::whereDate('start', $tomorrow)
                        ->where('status', Booking::$STATUS_ACTIVE)
                        ->get();

        if( $bookings->isEmpty() ) {
            return;
        }

        foreach($bookings as $booking) {
            $booking->sendBookingReminder();
        }
    }
}