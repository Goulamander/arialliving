<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingPage extends Model
{
    //
    /**
	 * The table associated with the model.
	 *
	 * @var string
	 */
    protected $table = 'building_page';
    

    /**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'building_id',
		'content',
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
	 * Building
	 * @return App\Models\Building
	 */
	function building() {
		return $this->belongsTo('App\Models\Building');
    }
    
    
}
