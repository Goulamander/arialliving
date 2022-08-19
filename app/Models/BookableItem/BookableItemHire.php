<?php

namespace App\Models\BookableItem;

use App\Models\BookableItem;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class BookableItemHire extends Model
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
    protected $table = 'bookable_item_hire';
    
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bookable_item_id',
        'available_qty',
        'allow_multiple',
        'allow_multiple_max',
        'item_price',
        'item_price_unit',
        'bond_amount',
        'booking_max_length',
        'booking_min_length',
        'allow_multiday',
        'booking_gap',
        'low_availability'
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
