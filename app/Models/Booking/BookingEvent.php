<?php

namespace App\Models\Booking;

use App\Models\Booking;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class BookingEvent extends Model
{
   
    public $timestamps = false;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'booking_event';
    
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'booking_id',
        'booking_status',
        'attendees_num',
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
	 * Parent Booking
	 * @return App\Models\Booking
	 */
    public function booking()
    {
        return $this->belongsTo('App\Models\Booking');
    }


    
	/***********************************************************************/
	/*************************  HELPER FUNCTIONS  **************************/
    /***********************************************************************/
    

    /***********************************************************************/
	/**************************  PUBLIC METHODS  ***************************/
	/***********************************************************************/

    /**
	 * Get the Attendees Number
	 *
	 * @return string
	 */
	public function getAttendeesNumber() {
		return (int) ($this->attendees_num && $this->attendees_num > 0) ? $this->attendees_num : 1;
	}
    
}
