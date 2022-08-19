<?php

namespace App\Models;

use App\Models\User;
use App\Models\Booking;
use App\Models\BookableItem;
use App\Models\Comment;
use App\Models\BuildingPage;
use App\Models\Pivot\BuildingUser;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;
use File;
use Storage;
use Auth;
use Session;

class Building extends Model
{
	
	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'buildings';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'street_address_1',
		'street_address_2',
		'suburb',
		'postcode',
		'state',
		'is_thumb',
		// Contact
		'contact_name',
		'mobile',
		'phone',
		'email',
		// Office hours
		'office_hours',
	];


	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [];



	/***********************************************************************/
	/****************************** FORM CONFIGS  **************************/
	/***********************************************************************/

	/**
	 * Create / Edit a Building profile form options
	 * 
	 */
	public static $form_fields = [
		
		'row_start',
			'div_start|class:col-md-7',

				'title:Building details',
				//
				'name' => [
					'validation' => 'required|max:255',
					'class' 	 => [
						'group'  => '',
						'input'  => '',
					],
					'label' 	 => 'Building Name',
					'type'		 => 'text',
				],
				//
				'street_address_1'  => [
					'validation' => 'required|max:255',
					'class' 	 => [
						'group' => '',
						'input' => 'autocompleteAddress_OFF_need_billing_setup',
					],
					'label' 	 => 'Street address',
					'type'		 => 'text'
				],
				//
				'street_address_2'  => [
					'validation' => 'max:255',
					'class' 	 => [
						'group' => '',
						'input' => '',
					],
					'label' 	 => 'Street address line 2',
					'type'		 => 'text'
				],
				// insert row start
				'row_start',
					//
					'suburb'  => [
						'validation' => 'required|max:255',
						'class' 	 => [
							'group' => 'col-6',
							'input' => '',
						],
						'label' 	 => 'Suburb',
						'type'		 => 'text'
					],
					//
					'state' => [
						'validation' => 'required',
						'class' 	 => [
							'group' => 'col',
							'input' => '',
						],
						'label' 	 => 'State',
						'type'		 => 'select',
						'options'	 => [
							'QLD' 	=> 'QLD',
							'ACT' 	=> 'ACT',
							'NSW' 	=> 'NSW',
							'NT' 	=> 'NT',
							'SA' 	=> 'SA',
							'TAS' 	=> 'TAS',
							'VIC' 	=> 'VIC',
							'WA' 	=> 'WA',
						],
						'value' => 'QLD'
					],
					//
					'postcode'  => [
						'validation' => 'required|digits:4',
						'class' 	 => [
							'group' => 'col',
							'input' => '',
						],
						'label' 	 => 'Postcode',
						'type'		 => 'number'
					],
				// insert row end
				'row_end',

			'div_end',
			'div_start|class:col-md-5',

				'title:Onsite Contact',
				//
				'contact_name' => [
					'validation' => 'max:255',
					'label' 	 => 'Contact Name',
					'type'		 => 'text',
					'class' 	 => [
						'group' => '',
						'input' => '',
					],
				],
				//
				'email' => [
					'validation' => 'max:255',
					'label' 	 => 'Email',
					'type'		 => 'email',
					'class' 	 => [
						'group' => '',
						'input' => '',
					],
				],
				//
				'phone'  => [
					'validation' => 'max:255',
					'class' 	 => [
						'group' => '',
						'input' => 'phone-number',
					],
					'label' 	 => 'Phone',
					'type'		 => 'text'
				],
				//
				'mobile'  => [
					'validation' => 'max:255',
					'class' 	 => [
						'group' => '',
						'input' => 'mobile-number',
					],
					'label' 	 => 'Mobile',
					'type'		 => 'text'
				],
			'div_end',
		'row_end'	
	];

	/**
	 * Create / Edit a Building profile form options
	 * 
	 */
	public static $office_hours_fields = [
		'row_start',
			'div_start|class:col-md-9',
				//
				'office_hours' => [
					'validation' => '',
					'class' 	 => [
						'group'  => '',
						'input'  => '',
					],
					'label'	=> 'Office Hours',
					'type'  => 'office-hours',
					'tooltip' => 'Following day or weekend bookings must made within this time range.'
				],
			'div_end',
		'row_end'	
	];



	/***********************************************************************/
	/************************* ELOQUENT RELATIONSHIPS **********************/
	/***********************************************************************/
  

	/**
	 * Users
	 * @return App\Models\Users thru (App\Models\Pivot\UserBuilding)
	 */
	function users() {
		return $this->belongsToMany('App\Models\User')
					->withPivot([
						'unit_no',
						'relation_start',
						'relation_end',
						'relation_status',
						'relation_type'
					]);
	}

	
	/**
	 * Bookings
	 * @return App\Models\Booking
	 */
	public function bookings() {
		return $this->hasMany('App\Models\Booking');
	}


	/**
	 * Bookable items
	 * @return App\Models\BookableItem
	 */
	public function bookableItems() {
		return $this->hasMany('App\Models\BookableItem')
					->with('room')
					->with('event')
					->with('hire')
					->with('service')
					->orderBy('order');
	}


	/**
	 * Get Building Retail Deals
	 * @return App\Models\RetailDeal thru App\Models\RetailStore
	 */
	public function retailStores() {
		return $this->hasMany('App\Models\RetailStore')->where('status', 1);
	}


	/**
	 * Get the active Building Retail Deals
	 * @return App\Models\RetailDeal thru App\Models\RetailStore
	 */
	public function retailDeals() {
		return $this->hasManyThrough(
				'App\Models\RetailDeal',
				'App\Models\RetailStore',		
				'building_id', // Foreign key on retail_stores table...
				'store_id', // Foreign key on retail_deals table...
			)
			->where('retail_deals.status', 1)
			->orderBy('retail_deals.id', 'DESC');
	}


	/**
	 * Get all Building Retail Deals
	 * @return App\Models\RetailDeal thru App\Models\RetailStore
	 */
	public function retailDealsAll() {
		return $this->hasManyThrough(
			'App\Models\RetailDeal',
			'App\Models\RetailStore',		
            'building_id',
            'store_id',
		);
	}


	/**
	 * Comments
	 * @return App\Models\Comment
	 */
	public function comments() {
		return $this->hasMany('App\Models\Comment', 'building_id', 'id');
	}


	/**
	 * Building page content
	 * @param App\Models\BuildingPage
	 */
	public function page_content() {
		return $this->hasOne('App\Models\BuildingPage');
	}

	



	/***********************************************************************/
	/***************************  LOCAL SCOPES  ****************************/
	/***********************************************************************/


	function scopeMyBuildings($query) 
	{
		// SuperAdmin, Admin
		if(Auth::user()->isSuperAdmin() || Auth::user()->isAdmin()) {
			return $query;
		}

		// Own only: Building Manager, Staff, 3rd party
		return $query->leftJoin('building_user', 'building_id', '=', 'buildings.id')
					->where('building_user.user_id', Auth::id())
					->where('relation_status', true)
					->groupBy('building_user.building_id');
	}





	/***********************************************************************/
	/*************************** PUBLIC METHODS  ***************************/
	/***********************************************************************/

	
	/******** File Management ********/

	/**
	 * Return with the card image path
	 */
	public function imagePath() {
		return '/buildings/'.$this->id .'/';
	}

	/**
	 * Return with the gallery images path
	 */
	public function galleryPath() {
		return '/buildings/'.$this->id .'/gallery';
	}

	/**
	 * Return with the terms attachments path
	 */
	public function termsPath() {
		return '/buildings/'.$this->id .'/terms';
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
		return $first_only ? reset($i) : $i;
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


	/** Sort Outs  */



	/**
	 * Get the thumbnail
	 * 
	 * @param array $size array('180x180', '820x500')
	 * @return mixed thumbnail_url || false
	 */
	public function getThumb($size = '180x180') {

		if( !in_array($size, ['180x180', '820x500']) ) {
			$size = '180x180';
		}
		return $this->is_thumb ? Storage::disk('public')->url('buildings/'.$this->id.'/'.$this->is_thumb.'_'.$size.'.jpg') : false;
	}
	
	public function getThumbWithoutDomain($size = '180x180') {

		if( !in_array($size, ['180x180', '820x500']) ) {
			$size = '180x180';
		}
		return $this->is_thumb ? 'storage/buildings/'.$this->id.'/'.$this->is_thumb.'_'.$size.'.jpg' : false;
	}
	
	public static function getThumbStatic($id, $is_thumb, $size = '180x180') {

		if( !in_array($size, ['180x180', '820x500']) ) {
			$size = '180x180';
		}
		return $is_thumb ? Storage::disk('public')->url('buildings/'.$id.'/'.$is_thumb.'_'.$size.'.jpg') : false;
	}





	/**
	 * Get building thumb or initials
	 * 
	 * @param str - size
	 * @return str
	 */
	public function ThumbOrInitials($size = 's') {

		if($this->is_thumb) {
			return '<span data-exclude="true" class="initials _bg" style="background-image: url('.$this->getThumb($size).')"></span>';
		}

		return '<span data-exclude="true" class="initials">'.initials($this->name).'</span>';
	}


	/**
	 * Get building thumb or initials (static)
	 * 
	 * @param int - building_id
	 * @param str - building_name
	 * @param str - thumb (file name)
	 * @param str - size
	 * 
	 * @return str
	 * 
	 */
	public static function getThumbOrInitials($building_id, $building_name, $thumb, $size = 's') {

		if($thumb) {
			return '<span data-exclude="true" class="initials _bg" style="background-image: url('.self::getThumbStatic($building_id, $thumb, $size).')"></span>';
		}

		return '<span data-exclude="true" class="initials">'.initials($building_name).'</span>';
	}


	/**
	 * Get the status of a building
	 */
	public function getResidencyStatus($is_label = true) {
		
		switch($this->pivot->relation_status) {

			case BuildingUser::$STATUS_ACTIVE:
				return $is_label ? '<span class="label l-green">Active</span>' : 'Inactive';
				break;

			case BuildingUser::$STATUS_INACTIVE:
				return $is_label ? '<span class="label l-gray">Inactive</span>' : 'Inactive';
				break;
			
		}
	}

	/** 
	 * Return the full address
	 * 
	 */
	public function fullAddress() {

		$street_address = $this->street_address_2 ? $this->street_address_2.' / '.$this->street_address_1 : $this->street_address_1;
		return $street_address.', '.$this->suburb.' '.$this->postcode.' '.$this->state;
	}



	/**
	 * Get comments
	 * 
	 * @param int $offset
	 * @param int $limit
	 * @return
	 */
	public function get_comments($offset = 0, $limit = 30) {
		$comment = new Comment();
		return $comment->get_comments(['building_id' => $this->id], $offset, $limit);		
	}



	/**
	 * 
	 */
	public static function getOrSetBuilding(array $with_relations = []) {

		if( Auth::user()->isResident() ) {

			$building = Auth::user()
				->building->first();
				// ->with($with_relations)
				// ->first();

        // for the back-end users add all buildings in so they can toggle between them.
		}
		else {
        
            $buildings = self::myBuildings()
				->orderBy('name', 'ASC')
				->with($with_relations)
                ->get();

            if(!$buildings) {
                abort(404);
            }
            
            if(!Session::get('building_preview_id') && count($buildings) > 0) {
                Session::put('building_preview_id', $buildings[0]->id);
            }   

            $building = $buildings->filter(function($building) {
                    return $building->id == Session::get('building_preview_id');
                })->first();
		}
		
		return $building;

	}



}
