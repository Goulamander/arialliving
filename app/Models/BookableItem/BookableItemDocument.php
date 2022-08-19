<?php

namespace App\Models\Booking;

use App\Models\Booking;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;


class BookableItemDocument extends Model
{
    
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bookable_item_documents';
    
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bookable_item_id',
        'name',
        'file_path',
        'uploaded_by',
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
