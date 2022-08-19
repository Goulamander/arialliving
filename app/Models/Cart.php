<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class cart extends Model
{

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
    protected $table = 'cart';
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'item_id',
        'items',
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
    public function user()
    {
        return $this->belongsTo('App\Models\User', 'id', 'user_id');
    }


    /**
     * User
     * @return App\Models\User
     */
    public function bookableItem()
    {
        return $this->belongsTo('App\Models\BookableItem', 'id', 'item_id');
    }


    
}
