<?php

namespace App\Models;

use App\Models\User;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Support\Arr;

use Carbon\Carbon;
use Storage;
use Auth;

class RetailStore extends Model
{

	use SoftDeletes;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'retail_stores';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'building_id',
		'user_id',
		'name',
		'description',
		'status',
		'thumb'
	];


	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [];



	// Retail Store Status
	public static $STATUS_INACTIVE 	= 0; 
	public static $STATUS_ACTIVE 	= 1;
	public static $STATUS_DELETED 	= 2;


	/***********************************************************************/
	/****************************** FORM CONFIGS  **************************/
	/***********************************************************************/


	/**
	 * Create / Edit a Retail Store
	 * 
	 * @return array -- form fields
	 */
	public static function form_fields() {
		
		// add the resident levels
		$managers = User::selectRaw("users.id, concat(users.first_name, ' ', users.last_name) as name")
					->where('role_id', User::$ROLE_EXTERNAL)
					->get()
					->keyBy('id')
					->toArray();

		$managers = array_map(function($r) {
			return $r['name'];
		}, $managers);

		
		$managers = Arr::add($managers, '', 'No manager');

		return [
			//
			'name' => [
				'validation' => 'required|max:255',
				'class' 	 => [
					'group'  => '',
					'input'  => '',
				],
				'label' 	 => 'Store Name',
				'type'		 => 'text',
			],
			'description' => [
				'validation' => '',
				'class' 	 => [
					'group'  => '',
					'input'  => '',
				],
				'label' 	 => 'Short Description',
				'type'		 => 'textarea',
			],
			//
			'title:Building & Store Manager',
			//
			'building_id' => [
				'object_key'  => 'building.id',
				'validation'  => 'required',
				'class' 	 => [
					'group'  => '',
					'input'  => '',
				],
				'label' 	 => 'Building',
				'type'		 => 'select',
				'options'	 => [],
				'data'		 => [
					's2' => true,
					'source' => 'building',
					'return' => 'id',
				],
				's2_selected_opt_keys' => [
					'name' => 'building.name',
					'postcode' => 'building.postcode',
					'suburb' => 'building.suburb',
				]
			],
			'user_id' => [
				'validation' => '',
				'class' 	 => [
					'group' => '',
					'input' => '',
				],
				'label' 	 => 'Store Manager Access',
				'description'=> '',
				'type'		 => 'select',
				'options'	 => $managers,
				'description' => 'Select a user with 3rd party access.',
				'value' => ''
			],
		];
	}



	/***********************************************************************/
	/************************* ELOQUENT RELATIONSHIPS **********************/
	/***********************************************************************/


	/**
	 * Get the Store's deals
	 * @return App\Models\RetailDeal
	 */
	public function deals() {
		return $this->hasMany('App\Models\RetailDeal', 'store_id');
	}


	/**
	 * Get the Store's Building
	 * @return App\Models\Building
	 */
	public function building() {
		return $this->belongsTo('App\Models\Building');
	}


	/**
	 * Redeem Users
	 * @return App\Models\Users thru (App\Models\Pivot\UserDealRedeems)
	 */
	public function user() {
		return $this->belongsTo('App\Models\User');
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
			return $query->whereIn('building_user.building_id', $building_ids);
		}

		// 3rd party
		if( Auth::user()->isExternal() ) {
			return $query->where('user_id', Auth::id());
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
		return '/stores/'.$this->id.'/';
	}



	/**
	 * Return with the gallery images path
	 * @return str folder_path
	 */
	public function galleryPath() {
		return '/stores/'.$this->id .'/gallery';
	}



	/**
	 * Get the images for the gallery
	 * @return array image urls
	 */
	public function getGalleryImages($first_only = false) {
		
		$images = Storage::disk('public')->allFiles($this->galleryPath());

		if(!$images) return $first_only ? '' : [];

		$i = array_filter($images, function($img) {
			return strpos($img, ".jpg") !== false && strpos($img, "_thumb") === false;
		});

		return $first_only ? $i[0] : $i;
	}

	/**
	 * Get the thumbs of the gallery images
	 * @return array thumbnail urls
	 */
	public function getGalleryThumbs() {

		$images = Storage::disk('public')->allFiles($this->galleryPath());

		if(!$images) return [];

		return array_filter($images, function($img) {
			return strpos($img, "_thumb.jpg") !== false;
		});
	}



	/**
	 * Get the thumb of this deal
	 */
	public function getThumb($size = '180x180') {

		if( !in_array($size, ['180x180', '820x500']) ) {
			$size = '180x180';
		}
		return $this->thumb ? Storage::disk('public')->url($this->imagePath().$this->thumb.'_'.$size.'.jpg') : false;
	}
	


	/**
	 * Get the store's thumbnail
	 * 
	 * @param str $size
	 * @return str $file_path 
	 */
	public function thumbOrInitials($size = 's') {

		if($this->thumb) {
			return '<span data-exclude="true" class="initials _bg" style="background-image: url('.$this->getThumb($size).')"></span>';
		}
		return '<span data-exclude="true" class="initials">'.initials($this->name).'</span>';
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


	/**
	 * Soft Delete a retail s, or if it has already soft deleted remove permanently.
	 *
	 * @param int $user_id
	 * @return void
	 */
	static function deleteStore($store_id) {

		$store = RetailStore::withTrashed()->find($store_id);

		// Delete permanently
		if( $store->trashed() ) {
			$store->forceDelete();
			return false;
		}

		$store->deleted_at = Carbon::now()->toDateTimeString();
		$store->status = self::$STATUS_DELETED;
		$store->save();

		return $store;

	}



}