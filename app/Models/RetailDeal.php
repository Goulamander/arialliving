<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;
use Storage;
use Auth;

class RetailDeal extends Model
{
	
	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'retail_deals';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'title',
		'subtitle',
		'description',
		'terms',
		'allowed_redeem_num',
		'status',
		'thumb',
		'created_by',
	];


	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [];



	// Retail Deal Status
	public static $STATUS_INACTIVE 	= 0; 
	public static $STATUS_ACTIVE 	= 1;
	public static $STATUS_DELETED 	= 2;

	


	/***********************************************************************/
	/****************************** FORM CONFIGS  **************************/
	/***********************************************************************/


	/**
	 * Create / Edit a Retail Deal
	 * 
	 * @return array -- form fields
	 */
	public static function form_fields() {
		
		return [
			'title' => [
				'validation' => 'required|max:255',
				'class' 	 => [
					'group'  => '',
					'input'  => '',
				],
				'label' 	 => 'Title',
				'type'		 => 'text',
			],
			'subtitle' => [
				'validation' => 'max:255',
				'class' 	 => [
					'group'  => '',
					'input'  => '',
				],
				'label' 	 => 'Subtitle',
				'type'		 => 'text',
			],
			//
			'description' => [
				'validation' => '',
				'class' 	 => [
					'group'  => '',
					'input'  => '',
				],
				'label' 	 => 'Short Description',
				'type'		 => 'textarea',
			],
			'terms' => [
				'validation' => '',
				'class' 	 => [
					'group'  => '',
					'input'  => '',
				],
				'label' 	 => 'Deal Terms',
				'type'		 => 'textarea',
			],
			//
			'row_start',
			//
			'allowed_redeem_num' => [
				'validation' => '',
				'class' 	 => [
					'group'  => 'col',
					'input'  => '',
				],
				'label' 	 => 'Number of allowed redeem per resident',
				'type'		 => 'number',
				'value'      => 1,
				'description' => 'Leave empty for unlimited'
			],
			'status' => [
				'validation' => '',
				'class' 	 => [
					'group'  => 'col',
					'input'  => '',
				],
				'label' 	 => 'Status',
				'type'		 => 'select',
				'options'	 => [
					1 => 'Active',
					0 => 'Inactive',
				],
				'value' => 1,
			],
			'row_end'
		];
	}



	/***********************************************************************/
	/************************* ELOQUENT RELATIONSHIPS **********************/
	/***********************************************************************/


	/**
	 * Get the Store og the deal
	 * @return App\Models\Store
	 */
	public function store() {
		return $this->belongsTo('App\Models\RetailStore');
	}


	/**
	 * Redeem Users
	 * @return App\Models\Users thru (App\Models\Pivot\UserDealRedeems)
	 */
	public function redeemUsers() {
		return $this->belongsToMany('App\Models\User')
					->withPivot([
						'created_at'
					]);
	}


	/**
	 * Buildings
	 * @return App\Models\Building thru (App\Models\Pivot\BuildingReward)
	 */
	public function building() {
		return $this->belongsTo('App\Models\Building')->using('App\Models\Store');
	}



	/***********************************************************************/
	/****************************  LOCAL SCOPES  ***************************/
	/***********************************************************************/

	/**
	 * Get only residents.
	 */
	function scopeOwnOnly($query) {

		// SuperAdmin, Admin
		if(Auth::user()->isSuperAdmin() || Auth::user()->Admin()) {
			return $query;
		}

		// Building Manager, Staff (assigned buildings only)
		if( Auth::user()->BuildingManager() || Auth::user()->isStaff() ) {
			
			$building_ids = BuildingUser::where('user_id', Auth::id())->pluck('building_id')->toArray();
			
			return $query->whereIn('retail_stores.building_id', $building_ids)
				->leftJoin('retail_stores', 'retail_stores.id', 'retail_deals.store_id');
		}

		// 3rd party
		if( Auth::user()->isExternal() ) {
			return $query->where('retail_stores.user_id', Auth::id())
				->leftJoin('retail_stores', 'retail_stores.id', 'retail_deals.store_id');
		}
	}




	/***********************************************************************/
	/**************************  PUBLIC METHODS  ***************************/
	/***********************************************************************/


	/******** File Management ********/

	/**
	 * Return with the image path
	 * @return str folder_path
	 */
	public function imagePath() {
		return 'stores/'.$this->store_id.'/deals/'.$this->id.'/';
	}



	/**
	 * Get the deal's thumbnail
	 * 
	 * @param str $size
	 * @return str $file_path 
	 */
	public function getThumb($size = '180x180') {

		if( !in_array($size, ['180x180', '820x500']) ) {
			$size = '180x180';
		}
		return $this->thumb ? Storage::disk('public')->url($this->imagePath().$this->thumb.'_'.$size.'.jpg') : false;
	}



	/**
	 * Get the deal's thumb or return empty
	 * 
	 * @param str - size
	 * @return str
	 */
	public function thumbOrInitials($size = 's') {

		if($this->thumb) {
			return '<span data-exclude="true" class="initials _bg" style="background-image: url('.$this->getThumb($size).')"></span>';
		}
		return '';
	}



	/**
	 * Get the formatted status label
	 */
	public function getStatus($is_label = true) {
		
		switch($this->status) {

			case self::$STATUS_INACTIVE:
				return $is_label ? '<span class="label l-gray">Inactive</span>' : 'Inactive';
				break;

			case self::$STATUS_ACTIVE:
				return $is_label ? '<span class="label l-green">Active</span>' : 'Active';
				break;

			case self::$STATUS_DELETED:
				return $is_label ? '<span class="label l-red">Deleted</span>' : 'Active';
				break;
		}
	}





}