<?php

namespace App\Models;

use App\Mail\ResetPassword;

use App\Models\User;
use App\Models\Booking;
use App\Models\BookableItem;
use App\Models\Comment;
use App\Models\Transaction;
use App\Models\Role;
use App\Models\Pivot\BuildingUser;

use Auth;
use Carbon\Carbon;

use Mail;
use App\Mail\NormalEmail;

use CardDetect\Detector;
use Eway\Rapid as Eway;

use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

use Illuminate\Support\Facades\Hash;

use Lab404\Impersonate\Models\Impersonate;


class User extends Authenticatable
{
    use SoftDeletes;
	use Notifiable;

	protected $table = 'users';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'role_id',
		'first_name',
		'last_name',
		'email',
		'phone',
		'mobile',
		'is_flagged',
		'is_flagged_reason',
		'status',
		'tokenCustomerID',
		'password',
		'activated',
		'is_set_password',
	];

	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'password',
		'remember_token',
		'tokenCustomerID',
		'card_details'
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at'];


	// User Roles
	public static $ROLE_SUPER_ADMIN = 1; 
	public static $ROLE_BUILDING_MANAGER = 2; 
	public static $ROLE_ADMIN = 3; 
	public static $ROLE_STAFF = 4;
	public static $ROLE_EXTERNAL = 5; // 3rd party
	// Resident
	public static $ROLE_RESIDENT = 6; // Note: All above ID-5 are Custom Resident Levels
	public static $ROLE_RESIDENT_VIP = 7; // Note: All above ID-5 are Custom Resident Levels


	// User Status
	public static $STATUS_INACTIVE 	= 0; 
	public static $STATUS_ACTIVE 	= 1;
	public static $STATUS_INVITED 	= 2;
	public static $STATUS_FLAGGED 	= 3;
	public static $STATUS_DELETED 	= 5;


	 

	/***********************************************************************/
	/****************************** FORM CONFIGS  **************************/
	/***********************************************************************/

	/**
	 * Create / Edit a Resident profile fields
	 * 
	 * @return array -- form fields
	 */
	public static function resident_form_fields() {
		
		// add the resident levels
		$resident_levels = Role::select('id', 'display_name')
					->where('id', '>=', self::$ROLE_RESIDENT)
					->get()
					->keyBy('id')
					->toArray();

		$resident_levels = array_map(function($r) {
			return $r['display_name'];
		}, $resident_levels);


		$buildings = Building::get([
			'id', 
			'name', 
			'suburb', 
			'postcode', 
			'is_thumb'
		])
		->keyBy('id')
		->toArray();

		$buildings = array_map(function($r) {
			return $r['name'];
		}, $buildings);

		return [
			//
			'row_start',
			//
			'first_name' => [
				'validation' => 'required|max:255',
				'class' 	 => [
					'group'  => 'col',
					'input'  => '',
				],
				'label' 	 => 'First Name',
				'type'		 => 'text',
			],
			'last_name' => [
				'validation' => 'required|max:255',
				'class' 	 => [
					'group'  => 'col',
					'input'  => '',
				],
				'label' 	 => 'Last Name',
				'type'		 => 'text',
			],
			//
			'row_end',
			//
			'row_start',
			//
			'email' => [
				'validation' => 'required|max:255',
				'class' 	 => [
					'group'  => 'col',
					'input'  => '',
				],
				'label' 	 => 'Email',
				'type'		 => 'email',
			],
			//
			'mobile' => [
				'validation' => 'max:255',
				'class' 	 => [
					'group'  => 'col',
					'input'  => 'mobile-number',
				],
				'label' 	 => 'Mobile',
				'type'		 => 'text',
			],
			//
			'row_end',
			//
			'title:Residency details',
			//
			'role_id' => [
				'validation' => 'required',
				'class' 	 => [
					'group' => '',
					'input' => '',
				],
				'label' 	 => 'Resident Level',
				'description'=> '',
				'type'		 => 'select',
				'options'	 => $resident_levels,
			],
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
			//
			'row_start',
			//
			'unit_no' => [
				'object_key' => 'building.0.pivot.unit_no',
				'validation' => 'required|max:55',
				'class' 	 => [
					'group'  => 'col-3',
					'input'  => '',
				],
				'label' 	 => 'Unit No.',
				'type'		 => 'text',
			],
			'unit_type' => [
				'object_key' => 'building.0.pivot.unit_type',
				'validation' => 'required|max:55',
				'class' 	 => [
					'group'  => 'col-3',
					'input'  => '',
				],
				'label' 	 => 'Unit Type',
				'type'		 => 'select',
				'options'	 => [
					'1'	=> '1 Bed',
					'2'	=> '2 Bed',
					'3'	=> '3 Bed',
					'4'	=> '4 Bed',
				],
				'value' => 2
			],
			'relation_start' => [
				'object_key' => 'building.0.pivot.relation_start',
				'validation' => '',
				'class' 	 => [
					'group'  => 'col-3',
					'input'  => 'datePicker',
				],
				'label' 	 => 'Residency Start',
				'type'		 => 'date',
			],
			'relation_end' => [
				'object_key' => 'building.0.pivot.relation_end',
				'validation' => '',
				'class' 	 => [
					'group'  => 'col-3',
					'input'  => 'datePicker',
				],
				'label' 	 => 'Residency End',
				'type'		 => 'date',
			],
			//
			'row_end',
			//
			'invite_resident' => [
				'validation' => '',
				'class' 	 => [
					'group'  => 'checkbox mb-4 mt-3 float-right',
					'input'  => '',
				],
				'label' 	 => '',
				'type'		 => 'checkbox',
				'options'	 => [
					1 => 'Send invitation email to resident on creation?'
				],
				'hidden_on'	 => 'edit'
			],
		];
	}



	/**
	 * Create / Edit a User profile fields
	 * 
	 */
	public static function user_form_fields() {

		// add the resident levels
		$admin_levels = Role::select('id', 'display_name')
				->where('id', '<', self::$ROLE_RESIDENT)
				->get()
				->keyBy('id')
				->toArray();

		$admin_levels = array_map(function($r) {
			return $r['display_name'];
		}, $admin_levels);

		return [
			//
			'title:System Access',
			//
			'row_start',
			'role_id' => [
				'validation' => 'required',
				'class' 	 => [
					'group' => 'col-6',
					'input' => '',
				],
				'label' 	 => 'Admin Level',
				'type'		 => 'select',
				'options'	 => $admin_levels,
			],
			'row_end',
			//
			'title:Contact details',
			//
			'row_start',
			//
			'first_name' => [
				'validation' => 'required|max:255',
				'class' 	 => [
					'group'  => 'col',
					'input'  => '',
				],
				'label' 	 => 'First Name',
				'type'		 => 'text',
			],
			'last_name' => [
				'validation' => 'required|max:255',
				'class' 	 => [
					'group'  => 'col',
					'input'  => '',
				],
				'label' 	 => 'Last Name',
				'type'		 => 'text',
			],
			//
			'row_end',
			//
			'row_start',
			//
			'email' => [
				'validation' => 'required|max:255',
				'class' 	 => [
					'group'  => 'col',
					'input'  => '',
				],
				'label' 	 => 'Email',
				'type'		 => 'email',
			],
			//
			'mobile' => [
				'validation' => 'max:255',
				'class' 	 => [
					'group'  => 'col',
					'input'  => 'mobile-number',
				],
				'label' 	 => 'Mobile',
				'type'		 => 'text',
			],
			'row_end',
			//

			//
			'invite_user' => [
				'validation' => '',
				'class' 	 => [
					'group'  => 'checkbox mb-4 mt-3 float-right',
					'input'  => '',
				],
				'label' 	 => '',
				'type'		 => 'checkbox',
				'options'	 => [
					1 => 'Send invitation email to user on creation?'
				],
				'hidden_on'	 => 'edit'
			]
		];

	}



	/***********************************************************************/
	/************************* ELOQUENT RELATIONSHIPS **********************/
	/***********************************************************************/

	/**
	 * Get the role associated with the user.
	 *
	 * @return App\Models\Role
	 */
	function role()
	{
		return $this->belongsTo(Role::class);
	}


	/**
	 * Buildings
	 * @return App\Models\Building thru (App\Models\Pivot\UserBuilding)
	 */
	function allBuildings() {
		return $this->belongsToMany('App\Models\Building')
					->withPivot([
						'unit_no',
						'relation_start',
						'relation_end',
						'relation_status',
						'relation_type'
					])->orderBy('building_user.created_at', 'DESC');
	}


	/**
	 * Buildings
	 * @return App\Models\Building thru (App\Models\Pivot\UserBuilding)
	 */
	function buildings() {
		return $this->belongsToMany('App\Models\Building')
					->withPivot([
						'unit_no',
						'relation_start',
						'relation_end',
						'relation_status',
						'relation_type'
					])
					->wherePivot('relation_status', 1);
	}


	/**
	 * Resident's Current Building (todo: REMOVE)
	 * 
	 * @return App\Models\Building thru (App\Models\Pivot\UserBuilding)
	 */
	function building() {
		return $this->belongsToMany('App\Models\Building')
					->withPivot([
						'unit_no',
						'relation_start',
						'relation_end',
						'relation_status',
						'relation_type'
					])
					->wherePivot('relation_status', 1)->limit(1);
	}


	/**
	 * Bookings
	 * @return App\Models\Booking
	 */
	function bookings() {
		return $this->hasMany('App\Models\Booking');
	}


	/**
	 * Bookings
	 * @return App\Models\Booking
	 */
	function activeBookings() {
		return $this->hasMany('App\Models\Booking')
				->whereIn('status', [Booking::$STATUS_ACTIVE, Booking::$STATUS_CONFIRMED])
				->orderBy('bookings.start', 'ASC');
	}

	/**
	 * Bookings
	 * @return App\Models\Booking
	 */
	function activeBookingsFromNow() {
		return $this->hasMany('App\Models\Booking')
				->whereIn('status', [Booking::$STATUS_ACTIVE, Booking::$STATUS_CONFIRMED])
				->whereDate('start', '>=', Carbon::today())
				->orderBy('bookings.start', 'ASC');
	}


	/**
	 * Transactions
	 * @return App\Models\Transaction
	 */
	function transactions() {
		return $this->hasMany('App\Models\Transaction');
	}


	/**
	 * Comments
	 * @return App\Models\Comment
	 */
	public function comments() {
		return $this->hasMany('App\Models\Comment', 'resident_id', 'id');
	}


	/**
	 * Tenancy History
	 * @return App\Models\TenancyHistory
	 */
	function tenancyHistory() {
		return $this->hasMany('App\Models\TenancyHistory');
	}


	/**
	 * Get the user's Deal Redeems
	 * @return App\Models\RetailDeals
	 */
	function redeemedDeals() {
		return $this->belongsToMany('App\Models\RetailDeal', 'user_deal_redeems')
			->withTimestamps()
			->withPivot('code');
	}


	/**
	 * User Settings
	 */
	function settings() {
		return $this->hasOne('App\Models\UserSetting');
	}


	/***********************************************************************/
	/****************************  LOCAL SCOPES  ***************************/
	/***********************************************************************/


	/**
	 * Scope a query to get users by Role.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	function scopeUsers($query) {
		return $query->where('role_id', '<', self::$ROLE_RESIDENT);
	}


	/**
	 * Get only residents.
	 */
	function scopeResidents($query) {
		return $query->where('role_id', '>=', self::$ROLE_RESIDENT);
	}

	/**
	 * Get only residents.
	 */
	function scopeOwnOnly($query) {

		// SuperAdmin
		if(Auth::user()->isSuperAdmin()) {
			return $query;
		}

		// Admin: todo

		// Own only: Building Manager, Staff, 3rd party
		$building_ids = BuildingUser::where('user_id', Auth::id())->pluck('building_id')->toArray();
		return $query->whereIn('building_user.building_id', $building_ids);
	}


	/**
	 * Scope a query to get users by email.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder
	 * @param  string  $email
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	function scopeUserByEmail($query, $email) {
		return $query->where('email', $email)->first();
	}


	/***********************************************************************/
	/**************************  ROLE METHODS  ***************************/
	/***********************************************************************/
	
	
	/**
     * Super Admins
	 * 
     */
    public function isSuperAdmin() {
        return $this->role_id == self::$ROLE_SUPER_ADMIN ? true : false;
    }

    /**
     * Building Managers
	 * 
     */
    public function isBuildingManager() {
        return $this->role_id == self::$ROLE_BUILDING_MANAGER ? true : false;
    }

    /**
     * Admins
	 * 
     */
    public function isAdmin() {
        return $this->role_id == self::$ROLE_ADMIN ? true : false;
    }

    /**
     * Staff member. Cleaner, maintenance etc...
	 * 
     */
    public function isStaff() {
        return $this->role_id == self::$ROLE_STAFF ? true : false;
	}
	

    /**
     * External Service Provider
	 * 
     */
    public function isExternal() {
        return $this->role_id == self::$ROLE_EXTERNAL ? true : false;
    }


    /**
     * Resident levels. (Multiple)
	 *
     */
    public function isResident() {
        return $this->role_id == self::$ROLE_RESIDENT;
	}

	/**
     * Resident VIP
	 *
     */
    public function isResidentVip() {
		return $this->role_id == self::$ROLE_RESIDENT_VIP;
	}
	
	
	/**
	 * Check user role
	 */
	public function hasRole($role) {

		// an array of role names
		if( is_array($role) ) {
			return in_array($this->role->name, $role) ? true : false;
		}

		// by role_id
		if( is_numeric($role) ) {
			return $this->role_id == $role ? true : false;
		}

		// by role_name
		return $this->role->name == $role ? true : false;
	}


	/**
	 * Attach Role to user
	 * @param App\Models\Role
	 */
	public function attachRole($role) {

		if(!$role) {
			return false;
		}

		$this->role_id = $role->id;
		$this->save();		
	}


	/** Permissions */

	/**
	 * Can this user delete data?
	 * @return bool
	 */
	public function canDelete() 
	{
		return ($this->isSuperAdmin() || $this->isBuildingManager()) ? true : false;	
	}

	/**
	 * Can this user impersonate other users?
	 * @return bool
	 */
    public function canImpersonate()
    {
        return $this->isSuperAdmin() ? true : false;
	}

	public function canBeImpersonated() {
		return $this->isSuperAdmin() ? false : true;
	}

	/**
	 * Can this user be invited?
	 * @return bool
	 */
	public function canBeInvited()
	{
		return in_array($this->status, [self::$STATUS_INVITED, self::$STATUS_INACTIVE]) ? true : false;	
	}




	/***********************************************************************/
	/**************************  PUBLIC METHODS  ***************************/
	/***********************************************************************/



	/**
	 * Create or update user credit card
	 * 
	 * @param Request->all() as $data
	 * @return array
	 */
	public function storeCreditCard(array $data) {

		$data = (object) $data;

		$client = Eway::createClient(
			config('eway.api_key'),
			config('eway.api_password'),
			config('eway.endpoint')
		);

		// Store the card details
		$user = [
			'Title'     => 'Mr.',
			'FirstName' => $this->first_name,
			'LastName'  => $this->last_name,
			'Country'   => 'au',
			'CardDetails' => [
				'Name'        => $data->card_name,
				'Number'      => $data->card_number,
				'ExpiryMonth' => $data->card_expiry_month,
				'ExpiryYear'  => $data->card_expiry_year,
				'CVN'         => $data->card_cvn,
			]
		];

		// Create the user on eWay for future payment processing
		if($this->tokenCustomerID) {
			$user['TokenCustomerID'] = $this->tokenCustomerID;
			$eway_response = $client->updateCustomer(Eway\Enum\ApiMethod::DIRECT, $user);
		}
		else {
			$eway_response = $client->createCustomer(Eway\Enum\ApiMethod::DIRECT, $user);
		}

		// eWay has errors in response
		if( $eway_response->getErrors() ) {
						
			$errors = array_map(function($error) {
				$replace = [
					'EWAY_CARDEXPIRYMONTH' => 'Card Expiry Month',
					'EWAY_CARDEXPIRYYEAR' => 'Card Expiry Year',
					'EWAY_CARDNUMBER' => 'Card Number',
					'EWAY_CARDCVN' => 'Card CVN Number'
				];
				return strtr(Eway::getMessage($error), $replace);
			}, $eway_response->getErrors());

			return [
				'status' => false,
				'errors' => $errors
			];
		}

		// Get card type (visa, master, etc). the returned card number has some X-ed out numbers in the middle, fix that so it can be passed to the detector.
		$detector = new Detector();
		$card_type = $detector->detect(str_replace('X', '0', $eway_response->Customer->CardDetails->Number));

		// Store (add or update) the TokenCustomerID
		$this->tokenCustomerID = $eway_response->Customer->TokenCustomerID;

		$this->card_details = json_encode([
			"type" => $card_type,
			"end"  => substr($eway_response->Customer->CardDetails->Number, -4), // last 4 digit please
			"exp_year"  => $data->card_expiry_year, 
			"exp_month" => $data->card_expiry_month
		]);
		$this->save();

		return [
			'status' => true,
			'tokenCustomerID' => $eway_response->Customer->TokenCustomerID
		];
	}

	/**
	 * Make a direct transaction on the user's stored card
	 * 
	 * @param decimal $amount
	 * @return array
	 */
	public function makeDirectTransaction($amount) {

		$client = Eway::createClient(
			config('eway.api_key'),
			config('eway.api_password'),
			config('eway.endpoint')
		);

		// build the transaction body
		$transaction = [
			'Customer' => [
				'TokenCustomerID' => $this->tokenCustomerID,
			],
			'Payment' => [
				'TotalAmount' => $amount * 100,
			],
			'TransactionType' => Eway\Enum\TransactionType::RECURRING,
			'Capture' => true,
		];

		$Response = $client->createTransaction(Eway\Enum\ApiMethod::DIRECT, $transaction);
	
		//dd($Response);
		// eWay has errors in response
		if( $Response->getErrors() ) {
						
			$errors = array_map(function($error) {
				return Eway::getMessage($error);
			}, $Response->getErrors());

			return [
				'status' => false,
				'errors' => $errors
			];
		}

		return [
			'status' => true,
			'response' => $Response
		];
	}




	/**
     * Route notifications for the Nexmo channel.
     *
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return string
     */
    public function routeNotificationForNexmo($notification)
    {
        return str_replace(' ', '', $this->mobile);
	}
	

	/**
	 * Count the active booking of the user
	 */
	public function countActiveBookings() {

		$num = Booking::where([
			'user_id' => $this->id,
			'status' => Booking::$STATUS_ACTIVE,
		])->count();

		return $num;
	}

	
	/**
	 * Update this user's password
	 *
	 * @param  string  $new_password
	 * @return void
	 */
	function updatePassword($new_password)
	{
		$this->update([
			'password' => Hash::make($new_password),
		]);
	}


	/**
	 * Get the full name of user.
	 */
	public function fullName() {
		return $this->first_name.' '.$this->last_name;
	}


	/**
	 * Get the status of a user.
	 */
	public function getStatus($is_label = true) {
		
		switch($this->status) {

			case self::$STATUS_INACTIVE:
				return $is_label ? '<span class="label l-gray">Inactive</span>' : 'Inactive';
				break;

			case self::$STATUS_ACTIVE:
				return $is_label ? '<span class="label l-green">Active</span>' : 'Active';
				break;

			case self::$STATUS_INVITED:
				return $is_label ? '<span class="label l-yellow">Invited</span>' : 'Invited';
				break;

			case self::$STATUS_FLAGGED:
				return $is_label ? '<span class="label l-red" data-tippy-content="'.$this->is_flagged_reason.'">Flagged</span>' : 'Flagged';
				break;

			case self::$STATUS_DELETED:
				return $is_label ? '<span class="label l-gray">Deleted</span>' : 'Deleted';
				break;
			default:
				return '';
		}
	}


	/**
	 * Get the users Flag label (static)
	 * 
	 * @param $content - string
	 * @return html
	 */
	public static function getFlagLabel($content = null) {

		if($content) {
			return '<span class="label l-flag" data-tippy-content="'.$content.'"><i class="material-icons">flag</i></span>';
		}
		return  '<span class="label l-flag"><i class="material-icons">flag</i></span>';
	}

	

	/**
	 * Get the credit card's expiry date
	 */
	public function getCardExpiry() {

		if(!$this->card_details) {
			return '';
		}

		$card = json_decode($this->card_details);

		if($card->exp_year && $card->exp_month) {
			$full_exp_date = "20{$card->exp_year}-{$card->exp_month}";
			return Carbon::today()->diffInDays(Carbon::parse($full_exp_date)->endOfMonth(), false);
		}
		return '';
	}



		
	/**
	 * 
	 * Soft Delete a user, or if user has already soft deleted remove permanently.
	 *
	 * @param int $user_id
	 * @return void
	 */
	static function deleteUser($user_id)
	{

		$user = User::withTrashed()->find($user_id);

		// Delete permanently
		if( $user->trashed() ) {
			$user->forceDelete();
			return false;
		}

		// Set all building relations to inactive
		BuildingUser::where([
			'user_id' => $user->id,
			'relation_status' => BuildingUser::$STATUS_ACTIVE
			])
			->update([
				'relation_status' => BuildingUser::$STATUS_INACTIVE,
				'relation_end' => Carbon::now(),
			]);


		// Create the user activation objects for the users to be deleted
		$activation = new Activation();

		Activation::insert(['user_id' => $user_id, 'token' => $activation->generateToken()]);

		// User:: Soft Delete
		$user->activated = 0;
		$user->deleted_at = Carbon::now()->toDateTimeString();
		$user->status = self::$STATUS_DELETED;
		$user->save();
		
		return $user;
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
		return $comment->get_comments(['resident_id' => $this->id], $offset, $limit);		
	}




	

	/***********************************************************************/
	/*************************** STATIC METHODS ****************************/
	/***********************************************************************/
	


	/**
	 * Send the password reset notification.
	 *
	 * @param  string  $token
	 * @return void
	 */
	function sendPasswordResetNotification($token)
	{
		$email = $this->getEmailForPasswordReset();

		// Create notification email object
		$email_content = new ResetPassword($token, $email);

		// Send the email
		return Mail::to($email)->send($email_content);
	}


}
