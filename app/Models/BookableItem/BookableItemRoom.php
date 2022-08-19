<?php

namespace App\Models\BookableItem;

use App\Models\BookableItem;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class BookableItemRoom extends Model
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
    protected $table = 'bookable_item_room';
    
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bookable_item_id',
        'daily_booking_limit',
        'booking_max_length',
        'booking_min_length',
        'booking_from_time',
        'booking_to_time',
        'booking_gap',
        'allow_multiday',
        'low_availability',
        'is_resident_comment',
        'maximum_number_of_bookings_per_day'
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


	/***********************************************************************/
	/*************************  HELPER FUNCTIONS  **************************/
    /***********************************************************************/
    

    
}
