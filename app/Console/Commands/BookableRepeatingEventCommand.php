<?php

namespace App\Console\Commands;

// use App\Models\User;
// use App\Models\Building;
use App\Models\RecurringEvent;
//use App\Models\BookableItem;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Config;

class BookableRepeatingEventCommand extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookable:repeating-event';

    /**
     * The console command description.
     *
     * @var string
     */

    protected $description = 'Update bookable recurring_event update repeat_next';

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
        $today = Carbon::today();
        $this->info('Today - ' . $today);

        $recurringEvents = RecurringEvent::whereHas('item', function($q) {
                            $q->where('status', 1);
                        })->get();

        if( $recurringEvents->isEmpty() ) {
            return;
        }

        
        foreach($recurringEvents as $recurringEvent) {
            $data = (object) [
                "repeat_next" => $recurringEvent->repeat_next,
                "repeat_start" => $recurringEvent->repeat_start,
                "repeat_end" => $recurringEvent->repeat_end ?? NULL,
                "repeat_every" => $recurringEvent->repeat_every,
                "frequency" => $recurringEvent->frequency,
                "frequency_week_days" => $recurringEvent->frequency_week_days,
            ];
            $repeatNext = $recurringEvent->nextRepeatDate($data);
            if(isset($repeatNext)){
                $recurringEvent->repeat_next = $repeatNext;
                $recurringEvent->save();
                $this->info('Update(#' . $recurringEvent->id . ' - '. $recurringEvent->item->title .') with repeat_next is: ' . $repeatNext);
            }
        }
    }
}