<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Models\Invoice;
use App\Models\XeroSync;
use App\Models\Notification;
use App\Models\Setting;
use BladeExtensions;

use Carbon\Carbon;

class RecurringEvent extends Model
{
	public $timestamps = false;
	

	/**
     * Primary key
     */
	protected $primaryKey = 'bookable_item_id';
	

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'recurring_event';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
		'event_id',
		//
		'repeat_start',
		'repeat_next',
		'repeat_end',
		//
		'repeat_every',
		'frequency',
		'frequency_week_days',
    ];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];


    /**
     * Days of the Week
     *
     * @var array
     */
	public static $day_name_array = [
		1 => 'monday',
		2 => 'tuesday',
		3 => 'wednesday',
		4 => 'thursday',
		5 => 'friday',
		6 => 'saturday',
		0 => 'sunday'
	];

    /**
     * The recurring item frequency options
     *
     * @var array
     */
	public static $frequency_array = [
		7   => 'Week',
		30  => 'Month',
		365 => 'Year'
	];
	
    /**
     * Days of the Week
     *
     * @var array
     */
	public static $days_array = [
		1 => 'M',
		2 => 'T',
		3 => 'W',
		4 => 'T',
		5 => 'F',
		6 => 'S',
		0 => 'S'
	];



	public static $SUBMIT_AT = '7:00';

    /***********************************************************************/
    /* ELOQUENT RELATIONSHIPS
    /***********************************************************************/


    /**
     * Get the Event
     *
     * @return App\Models\Event
     */
    public function item() {
        return $this->belongsTo('App\Models\BookableItem', 'bookable_item_id');
    }






    /***********************************************************************/
    /* HELPER METHODS  
	/***********************************************************************/
	
    /**
     * Save or Drop recurring Item
	 * 
     * @param  $itemObj
     * @param  $array [event_id => $event_id]
     * @return boolean
     */
    public function SaveOrDrop($itemObj, $item) {

		if( ! $itemObj || ! $item) {
			return false;
		}

		$itemObj = (object) $itemObj;

		// Update / Create setting
        if( $itemObj->event_type == 2)
        {

            $recurring_item = $item->recurring()->updateOrCreate([], [
				'repeat_start' 	=> $itemObj->repeat_start,
				'repeat_end'	=> $itemObj->repeat_end ? $itemObj->repeat_end : NULL,
				'repeat_next'   => $itemObj->repeat_start,
				//
				'repeat_every'  => $itemObj->repeat_every,
				'frequency'    	=> $itemObj->frequency,
				'frequency_week_days' => implode(',', $itemObj->frequency_week_days)
			]);


			// Check for more loops
			if(! self::nextRepeatDate($recurring_item) ) 
			{
				// no more loops found, push this event to
				if( $recurring_item ) {

				}
			}
		} 
		// Remove setting       
        else {
			
			$recurring_item = self::where('bookable_item_id', $item->id)->first();

			if($recurring_item) {
				$recurring_item->delete(); // delete the recurring item
			}
			
		}
		
	}	



    /***********************************************************************/
    /* HELPER METHODS  
    /***********************************************************************/


    /**
     * Get readable recurring frequency
     *
     * @return string
     */
    public function getRecurringFrequency($label = false) {

		$freq = null;

        switch ($this->frequency) {
			case 1:
				$freq = $this->repeat_every == 1 ? 'Daily' : 'Every '. $this->repeat_every .' days';
                break;
			case 7:
				$freq = $this->repeat_every == 1 ? 'Weekly' : 'Every '. $this->repeat_every .' weeks';
                break;
			case 30:
				$freq = $this->repeat_every == 1 ? 'Monthly' : 'Every '. $this->repeat_every .' months';
                break;
			case 365:
				$freq = $this->repeat_every == 1 ? 'Annually' : 'Every '. $this->repeat_every .' years';
                break;
		}
		if($freq) {
			return (!$label) ? $freq : '<span class="recurring" title="Recurs '.$freq.'">'.$freq.'</span>';
		}
		return '';
    }



	/**
	 * Get readable recurring Date
	 *
	 * @return string date_period
	 */
	public function getShortcodeVal() {
		
		if(!$this->repeat_next) {
			return '-';
		}

		$start = Carbon::parse($this->repeat_next);
		$end   = Carbon::parse($this->repeat_next);

		$e = $this->repeat_every;

		switch ($this->frequency) 
		{
			// Week
			case 7:
				$this->invoicing_in == 1 ? $end = $end->addWeeks($e) : $start->addWeeks(-1 * $e);

			// Month
			case 30:
				$this->invoicing_in == 1 ? $end = $end->addMonths($e) : $start = $start->addMonths(-1 * $e);
				break;

			// Year
			case 365:
				$this->invoicing_in == 1 ? $end = $end->addYears($e) : $start = $start->addYears(-1 * $e);
				break;
		}
		$this->invoicing_in == 1 ? $end->addDays(-1) : $start->addDays(1);
	
		return dateFormat($start).' - '.dateFormat($end);
	}

	


	/**
	 * Get the next recurring date
	 *
	 * @return string
	 */
	public static function nextRepeatDate($data) {

		if( !$data || !in_array($data->frequency, [7,30,365]) ) {
			return null;
		}

		if(!$data->repeat_start) {
			return null;
		}

		$repeat_next = null;

		$repeat_start = Carbon::parse($data->repeat_start);
		$repeat_start_day_index = Carbon::parse($data->repeat_start)->dayOfWeek;

		// Repeat frequency
		switch($data->frequency) {

			// Week
			case '7':

				if(!$data->frequency_week_days) {
					return null;
				}

				if( gettype($data->frequency_week_days) == 'string' ) {
					$data->frequency_week_days = explode(',', $data->frequency_week_days);
				}

				$current_day_index = Carbon::now()->dayOfWeek;

				$next_day_on_this_week = self::findNext($data->frequency_week_days, $current_day_index);

				if($next_day_on_this_week) {
					$repeat_next = Carbon::today()->addWeeks($data->repeat_every - 1)->next(self::$day_name_array[$next_day_on_this_week]);
					$repeat_next = $repeat_next <= $repeat_start ? $repeat_start : $repeat_next;
				}
				else {
			
					// jump to next week(s)
					if( $data->repeat_every >=1) {
						$repeat_next = $repeat_start->addWeeks($data->repeat_every);
					}
					else {
						if($repeat_start_day_index == $data->frequency_week_days[0]) {
							// $repeat_next = $repeat_start;
							if(Carbon::now()->gte($repeat_start)){
								$repeat_next = Carbon::today();
							} else {
								$repeat_next = $repeat_start;
							}
						} else {
							$repeat_next = $repeat_start->next(self::$day_name_array[$data->frequency_week_days[0]]);
						}
					}

				}
				break;


			// Month
			case '30':

				if( $repeat_start <= Carbon::today() ) {
					$repeat_next = $repeat_start->addMonth($data->repeat_every);
				}
				else {
					$repeat_next = $repeat_start;
				}
			
				// Avoid Weekends
				if($repeat_next->dayOfWeek == Carbon::SATURDAY) {
					$repeat_next->addDays(-1);
				}
				if($repeat_next->dayOfWeek == Carbon::SUNDAY) {
					$repeat_next->addDays(1);
				}
				break;

			// Year
			case '365':
				if( $repeat_start <= Carbon::today() ) {
					$repeat_next = $repeat_start->addYear($data->repeat_every);
				}
				else {
					$repeat_next = $repeat_start;
				}

				// Avoid Weekends
				if($repeat_next->dayOfWeek == Carbon::SATURDAY) {
					$repeat_next->addDays(-1);
				}
				if($repeat_next->dayOfWeek == Carbon::SUNDAY) {
					$repeat_next->addDays(1);
				}
				break;			
		}

		return ( $data->repeat_end && (Carbon::parse($data->repeat_end) <= $repeat_next) ) ? null : $repeat_next->format('Y-m-d');
	}


	// Find the Next day 
	public static function findNext(array $days, $current)
	{
		foreach ($days as $key => $val) {
			if ($val > $current) {
				return $val;
			}
		}
		return null;
	}


	/**
	 * Do the repeating for events.
	 *
	 * @param (array) $events
	 * @return void
	 */
	public static function doRecurring($events) {

		foreach($events as $event)
		{

			
		}

	}



}
