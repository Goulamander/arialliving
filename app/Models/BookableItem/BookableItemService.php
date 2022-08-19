<?php

namespace App\Models\BookableItem;

use App\Models\BookableItem;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class BookableItemService extends Model
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
    protected $table = 'bookable_item_service';
    
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bookable_item_id',
        'date_field_name',
        'is_date',
        'hide_cart_functionality',
        'timeslot_to',
		'timeslot_from',
		'assign_to_user_id',
        'payment_to_aria',
        'session_length',
        'bond_amount',
        'hide_pricing',
        'booking_gap_time',
    ];

    public static $IS_DATE_NO_DATE = 0;
    public static $IS_DATE_ADD_DATE_ONLY = 1;
    public static $IS_DATE_ADD_DATE_AND_TIME = 2;
    public static $IS_DATE_ADD_DATE_AND_TIME_RESTRICTED = 3;
    public static $IS_DATE_ADD_TIMESLOT = 4;
    public static $IS_DATE_ADD_DATE_RESTRICTED = 5;


    // Payment to Aria
	public static $PAYMENT_TO_ARIA_YES = 1;
	public static $PAYMENT_TO_ARIA_NO = 0;

    // Hide Pricing
	public static $HIDE_PRICING_YES = 1;
	public static $HIDE_PRICING_NO = 0;
	

    // fill to bookings
    public static $HOURS_ARR = [
        // am
        "0:00", //"0:00 am",
        "0:30", //"0:30 am",
        "1:00", //"1:00 am",
        "1:30", //"1:30 am",
        "2:00", //"2:00 am",
        "2:30", //"2:30 am",
        "3:00", //"3:00 am",
        "3:30", //"3:30 am",
        "4:00", //"4:00 am",
        "4:30", //"4:30 am",
        "5:00", //"5:00 am",
        "5:30", //"5:30 am",
        "6:00", //"6:00 am",
        "6:30", //"6:30 am",
        "7:00", //"7:00 am",
        "7:30", //"7:30 am",
        "8:00", //"8:00 am",
        "8:30", //"8:30 am",
        "9:00", //"9:00 am",
        "9:30", //"9:30 am",
        "10:00", //"10:00 am",
        "10:30", //"10:30 am",
        "11:00", //"11:00 am",
        "11:30", //"11:30 am",
        "12:00", //"12:00 am",
        // pm
        "12:30", //"12:30 pm",
        "13:00", //"1:00 pm",
        "13:30", //"1:30 pm",
        "14:00", //"2:00 pm",
        "14:30", //"2:30 pm",
        "15:00", //"3:00 pm",
        "15:30", //"3:30 pm",
        "16:00", //"4:00 pm",
        "16:30", //"4:30 pm",
        "17:00", //"5:00 pm",
        "17:30", //"5:30 pm",
        "18:00", //"6:00 pm",
        "18:30", //"6:30 pm",
        "19:00", //"7:00 pm",
        "19:30", //"7:30 pm",
        "20:00", //"8:00 pm",
        "20:30", //"8:30 pm",
        "21:00", //"9:00 pm",
        "21:30", //"9:30 pm",
        "22:00", //"10:00 pm",
        "22:30", //"10:30 pm",
        "23:00", //"11:00 pm",
        "23:30", //"11:30 pm",
    ];

    // show on view
    public static $HOURS_24h_ARR = [
        // am
        "0:00 am",
        "0:30 am",
        "1:00 am",
        "1:30 am",
        "2:00 am",
        "2:30 am",
        "3:00 am",
        "3:30 am",
        "4:00 am",
        "4:30 am",
        "5:00 am",
        "5:30 am",
        "6:00 am",
        "6:30 am",
        "7:00 am",
        "7:30 am",
        "8:00 am",
        "8:30 am",
        "9:00 am",
        "9:30 am",
        "10:00 am",
        "10:30 am",
        "11:00 am",
        "11:30 am",
        "12:00 am",
        // pm
        "12:30 pm",
        "1:00 pm",
        "1:30 pm",
        "2:00 pm",
        "2:30 pm",
        "3:00 pm",
        "3:30 pm",
        "4:00 pm",
        "4:30 pm",
        "5:00 pm",
        "5:30 pm",
        "6:00 pm",
        "6:30 pm",
        "7:00 pm",
        "7:30 pm",
        "8:00 pm",
        "8:30 pm",
        "9:00 pm",
        "9:30 pm",
        "10:00 pm",
        "10:30 pm",
        "11:00 pm",
        "11:30 pm",
    ];


    public static $NAME_OF_DATE = [
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
        "Sunday"
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];


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
	 * Parent User
	 * @return App\Models\User
	 */
    public function assignTo()
    {
        return $this->hasOne('App\Models\User', 'id', 'assign_to_user_id')->with('settings');
    }

  
	/***********************************************************************/
	/*************************  HELPER FUNCTIONS  **************************/
    /***********************************************************************/





}
