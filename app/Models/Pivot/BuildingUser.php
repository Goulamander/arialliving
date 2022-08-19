<?php

namespace App\Models\Pivot;

use App\Models\User;
use App\Models\Building;

// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Support\Facades\Log;

use Illuminate\Database\Eloquent\Relations\Pivot;

use Carbon\Carbon;


class BuildingUser extends Pivot
{

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'building_user';

	// Residency Status
	public static $STATUS_ACTIVE = 1;
	public static $STATUS_INACTIVE = 0;

	// Residency Type
	public static $RELATION_TYPE_RESIDENT = 1;
	public static $RELATION_TYPE_MANAGEMENT = 2;
	public static $RELATION_TYPE_STAFF = 3;


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'building_id',
		'unit_no',
		'unit_type',
		'relation_start',
		'relation_end',
		'relation_status',
		'relation_type',
		'notes',
		//
		'created_at',
		'updated_at',
	];


	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [];



	public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
    
    public function building()
    {
        return $this->belongsTo('App\Models\Building', 'building_id', 'id');
    }
	
	




}
