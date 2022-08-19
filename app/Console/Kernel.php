<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    
    protected $commands = [
       // 
       Commands\BookingReminderCommand::class,
       Commands\OrganizeBookingsCommand::class,
       Commands\OrganizeEventsCommand::class,
       Commands\ProcessPaymentsCommand::class,
       Commands\AddManualBookingCommand::class,
       Commands\BookableRepeatingEventCommand::class,
    ];


    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule) {

        // Once per day at 9am
        $schedule->command('booking:send-reminders')->dailyAt('9:00');
        $schedule->command('booking:process-payments')->dailyAt('9:00');

        // Every 15min
        $schedule->command('booking:organize')->everyFifteenMinutes();
        $schedule->command('events:organize')->everyFifteenMinutes();

        // Bookable recurring_event update repeat next every 
        $schedule->command('bookable:repeating-event')->everyMinute()->appendOutputTo(storage_path('logs/bookable-repeating-event.log'));;
    }


    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
