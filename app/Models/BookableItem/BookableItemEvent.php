<?php

namespace App\Models\BookableItem;

use App\Models\BookableItem;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class BookableItemEvent extends Model
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
    protected $table = 'bookable_item_event';
    
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bookable_item_id',
        'location_name',
        'location_id',
        'event_type',
        'event_date',
        'all_day',
        'event_from',
        'event_to',
        'attendees_limit',
        'is_rsvp',
        'allow_guests',
        'low_seats'
    ];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];


	// Event type
    public static $TYPE_SINGLE = 1;
    public static $TYPE_REPEATING = 2;
	

 


    /***********************************************************************/
    /************************* ELOQUENT RELATIONSHIPS **********************/
    /***********************************************************************/

    /**
	 * Parent BookableItem
	 * @return App\Models\BookableItem
	 */
    public function bookableItem()
    {
        return $this->belongsTo('App\Models\BookableItem');
    }

    

    /**
	 * The Event location (referencing to Room/Area bookableItem)
	 * @return App\Models\BookableItem
	 */
    public function location()
    {
        return $this->hasOne('App\Models\BookableItem', 'id', 'location_id');
    }

    
	/***********************************************************************/
	/**************************  PUBLIC METHODS  ***************************/
    /***********************************************************************/
    
	/**
	 * Get the type of an event
	 */
	public function eventType() {

		if(!$this->event_type) {
			return "";
		}

		switch($this->event_type) {

			case self::$TYPE_SINGLE:
				return 'Single';
				break;

			case self::$TYPE_REPEATING:
				return 'Repeating';
				break;
				
			default:
				return '';
				break;
		}
    }
    

}
