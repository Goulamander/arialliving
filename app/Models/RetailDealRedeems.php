<?php

namespace App\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;

use Carbon\Carbon;
use Storage;
use Auth;

class RetailDealRedeems extends Model
{

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'user_deal_redeems';



	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'retail_deal_id',
		'code'
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
	 * Get the redeemed deal
	 * @return App\Models\RetailDeal
	 */
	public function deal() {
		return $this->hasMany('App\Models\RetailDeal', 'retail_deal_id')->withTrashed();
	}



	/**
	 * Redeem Users
	 * @return App\Models\Users
	 */
	public function user() {
		return $this->belongsTo('App\Models\User', 'user_id');
	}

}