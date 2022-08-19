<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookableItemFee extends Model
{

    public $timestamps = true;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
    protected $table = 'bookable_item_fee';


    // Free type
    public static $TYPE_CLEANING = 0;
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'bookable_item_id',
        'type',
        'name',
        'fee',
        'created_at',
        'updated_at'
    ];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];


    /***********************************************************************/
    /************************* ELOQUENT RELATIONSHIPS ***********************/
    /***********************************************************************/


    /**
     * User
     * @return App\Models\User
     */
    public function bookableItem()
    {
        return $this->belongsTo('App\Models\BookableItem', 'id', 'bookable_item_id');
    }
}
