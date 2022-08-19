<?php

namespace App\Models;


use Illuminate\Support\Arr;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Collection;

use App\Models\User;
use App\Models\Booking;
use App\Models\Comment;
use App\Models\BookableItem\BookableItemEvent;
use App\Models\BookableItem\BookableItemService;

use Spatie\Period\Period;
use Spatie\Period\Precision;
use Spatie\Period\PeriodCollection;
use Spatie\Period\Boundaries;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Storage;
use File;
use Auth;
use DB;


class BookableItem extends Model
{
    use SoftDeletes;


    /**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'bookable_items';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'title',
		'type',
		'building_id',
		'category_id',
		'status',
		'is_private',
		'description',
		'is_signature_required',
		'is_thumb',
		'is_free_as_admin',
		'is_free',
		'price_tag',
		'admin_fee',
		'office_hours',
		'ignore_office_hours',
		'prior_to_book_hours',
		'cancellation_cut_off',
		'booking_instructions',		
		'created_by',
		'order',
		'enable_booking_policy',
	];


	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [];


	/**
	 * The attributes that should be hidden for arrays.
	 *
	 * @var array
	 */
	protected $hidden = [
		'description',
		'booking_instructions'
	];


	// Item types
    public static $TYPE_ROOM = 1;
    public static $TYPE_HIRE = 2;
	public static $TYPE_EVENT = 3;
	public static $TYPE_SERVICE = 4;
	

	// Get the status label by status_id
	public static $TYPE_LABEL = [
		1 => 'room',
		2 => 'hire',
		3 => 'event',
		4 => 'service'
	];
	

	// Bookable item status
    public static $STATUS_DRAFT     = 0;
    public static $STATUS_ACTIVE    = 1;
    public static $STATUS_CANCELLED = 2;
	public static $STATUS_ARCHIVE   = 3;


	// enable_booking_policy
	public static $ENABLE_BOOKING_POLICY_ON = 1;
	public static $ENABLE_BOOKING_POLICY_OFF = 0;
	


	/***********************************************************************/
	/****************************** FORM CONFIGS  **************************/
	/***********************************************************************/

	/**
	 * Create / Edit a Bookable item general fields.
	 * 
	 */
	public static function form_fields() {

		// add the resident levels
		$categories = Category::select('id', 'name')
			->where('status', 1)
			// ->orderBy('order', 'ASC')
			->get()
			->keyBy('id')
			->toArray();

		$categories = array_map(function($r) {
			return $r['name'];
		}, $categories);

		return [
			'row_start',
				'title' => [
					'validation' => 'required|max:255',
					'class' 	 => [
						'group'  => 'col-8',
						'input'  => '',
					],
					'label' 	 => 'Title',
					'type'		 => 'text',
				],
				'category_id' => [
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col-4',
						'input'  => '',
					],
					'label'	=> 'Category',
					'type'  => 'select',
					'options' => $categories
				],
			'row_end',
			'title:Building',
			'building_id' => [
				'object_key' => 'building.id',
				'validation' => 'required',
				'class' 	 => [
					'group'  => '',
					'input'  => '',
				],
				'label' 	 => 'Select a building for this item',
				'type'		 => 'select',
				'options'	 => [],
				'data'		 => [
					's2' => true, // Select2
					'source' => 'building',
					'return' => 'id',
				],
				's2_selected_opt_keys' => [
					'name' => 'building.name',
					'postcode' => 'building.postcode',
					'suburb' => 'building.suburb',
				]
			]
		];
	}


	/**
	 * Item type: EVENT fields
	 * 
	 **/
	public static function form_event_fields() {
		
		return array_merge(self::form_fields(), [
			// Admin Fee
			'title: Admin Fee',
			'row_start',
			'admin_fee' => [
				'validation' => 'min:0',
				'class' 	 => [
					'group'  => 'col-4',
					'input'  => '',
				],
				'label' => 'Admin Fee (per attendee)',
				'description' => 'Leave empty when there is no fee',
				'type'		 => 'number',
			],
			'row_end',

			'title:Location & Type',
			'location_name'  => [
				'object_key' => 'event.location_name',
				'validation' => 'required|max:255',
				'class' 	 => [
					'group'  => '',
					'input'  => '',
				],
				'label' 	 => 'Event Location',
				'type'		 => 'text',
			],
			// Event type
			'event_type'  => [
				'object_key' => 'event.event_type',
				'validation' => 'required|max:255',
				'class' 	 => [
					'group'  => '',
					'input'  => '',
				],
				'label' 	 => 'Event type',
				'type'		 => 'select',
				'options'	 => [
					1 => 'Single Event',
					2 => 'Repeating Event'
				],
				'conditions'  => [
					[
						'fields' => 'recurring_group',
						'if_value' => 'is:2'
					],
					[
						'fields' => 'single_event_group',
						'if_value' => 'is:1'
					]
				],
				'value' => 1
			],
		
			'title: Event Dates',
			'div_start|class:row active _single_event_group',
				'event_date' => [
					'object_key' => 'event.event_date',
					'validation' => '',
					'class' 	 => [
						'group'  => 'col-3',
						'input'  => '',
					],
					'label'	=> 'Event Date',
					'type'  => 'date',
				],
				'all_day' => [
					'object_key' => 'event.all_day',
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col-3 align-self-center mt-4',
						'input'  => '',
					],
					'label'	=> '',
					'type'  => 'checkbox',
					'options' => [
						1 => 'All Day'
					],
					// 'value' => 1,
					'conditions' => [
						[
							'fields' => 'event_from|event_to',
							'if_value' => 'null'
						]
					]
				],
				'event_from' => [
					'object_key' => 'event.event_from',
					'validation' => '',
					'class' 	 => [
						'group'  => 'col-3',
						'input'  => '',
					],
					'label'	=> 'From',
					'type'  => 'time',
					'value' => '09:00:00'
				],
				'event_to' => [
					'object_key' => 'event.event_to',
					'validation' => '',
					'class' 	 => [
						'group'  => 'col-3',
						'input'  => '',
					],
					'label'	=> 'To',
					'type'  => 'time',
				],
			'row_end',
			'event_repeat_event_options' => [
				'validation' => '',
				'class' 	 => [
					'group'  => '',
					'input'  => '',
				],
				'label'	=> 'Repeating Event',
				'type'  => 'repeating-options',
			],

			// Attendees Limit
			'title: Attendees',
			'row_start',
				'attendees_limit' => [
					'object_key' => 'event.attendees_limit',
					'validation' => 'min:0',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label'	=> 'Max. number of attendees',
					'type'  => 'number',
					'description' => 'Leave empty for unlimited'
				],
				'allow_guests' => [
					'object_key' => 'event.allow_guests',
					'validation' => 'min:0|max:1000',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label'	=> 'Number of guests allowed',
					'type'  => 'number',
				],
				// 'is_rsvp' => [
				// 	'object_key' => 'event.is_rsvp',
				// 	'validation' => 'required',
				// 	'class' 	 => [
				// 		'group'  => 'col',
				// 		'input'  => '',
				// 	],
				// 	'label' 	 => 'Is RSVP available?',
				// 	'type'		 => 'radio',
				// 	'options' => [
				// 		'1' => 'Yes',
				// 		'0' => 'No',
				// 	],
				// 	'value' => 0,
				// 	'tooltip' => 'When RSVP enabled and event confirmation email will be sent to all participants.',
				// ],
			'row_end',

			// Booking policy
			// 'title:Booking policy',
			'row_start',
				'enable_booking_policy' => [
					'object_key' => 'enable_booking_policy',
					'validation' => '',
					'class' 	 => [
						'group'  => 'checkbox col-12 mt-3',
						'input'  => '',
					],
					'label' 	 => '',
					'type'		 => 'checkbox',
					'options'	 => [
						self::$ENABLE_BOOKING_POLICY_ON => 'Booking policy'
					],
					'value' => self::$ENABLE_BOOKING_POLICY_ON,
					'conditions' => [
						[
							'fields' => 'prior_to_book_hours',
							'if_value' => 'is:1'
						],
						[
							'fields' => 'cancellation_cut_off',
							'if_value' => 'is:1'
						],
					],
				],
				'prior_to_book_hours' => [
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label' 	 => 'Residents can make bookings up to:',
					'description' => 'before start time.',
					'type'		 => 'select',
					'options' => [
						'1' => '1 hr',
						'2' => '2 hrs',
						'3' => '3 hrs',
						'4' => '4 hrs',
						'6' => '6 hrs',
						'8' => '8 hrs',
						'12' => '12 hrs',
						'24' => '24 hrs',
						'48' => '48 hrs',
					]
				],
				'cancellation_cut_off' => [
					'validation' => 'required|min:0',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label' 	 => 'Bookings can be changed or cancelled, up to:',
					'description' => 'hours before their booking.',
					'type'		 => 'number',
					'value'		 => 24
				],
			'row_end'


		]);
	}
	

	/**
	 * Item type: SERVICE fields 
	 * 
	 **/ 
	public static function form_service_fields() {

		$party_access = User::select('id', 'first_name', 'last_name', 'email')
			->where('role_id', User::$ROLE_EXTERNAL)
			->get()
			->keyBy('id')
			->toArray();

		$party_access = array_map(function ($r) {
			return $r['first_name'] . ' ' . $r['last_name'] .' ('. $r['email'] .')';
		}, $party_access);

		// add the resident levels
		$categories = Category::select('id', 'name')
			->where('status', 1)
			->orderBy('order', 'ASC')
			->get()
			->keyBy('id')
			->toArray();

		$categories = array_map(function($r) {
			return $r['name'];
		}, $categories);

	return array_merge(/*self::form_fields()*/[], [
		'row_start',
				'title' => [
					'validation' => 'required|max:255',
					'class' 	 => [
						'group'  => 'col-8',
						'input'  => '',
					],
					'label' 	 => 'Title',
					'type'		 => 'text',
				],
				'category_id' => [
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col-4',
						'input'  => '',
					],
					'label'	=> 'Category',
					'type'  => 'select',
					'options' => $categories
				],
			'row_end',
			'title:Building',
			'building_id' => [
				'object_key' => 'building.id',
				'validation' => 'required',
				'class' 	 => [
					'group'  => '',
					'input'  => '',
				],
				'label' 	 => 'Select a building for this item',
				'type'		 => 'select',
				'options'	 => [],
				'data'		 => [
					's2' => true, // Select2
					'source' => 'building',
					'return' => 'id',
				],
				's2_selected_opt_keys' => [
					'name' => 'building.name',
					'postcode' => 'building.postcode',
					'suburb' => 'building.suburb',
				]
			],
			'title:Set Date & Time Fields',
			'row_start',
				'is_date' => [
					'object_key' => 'service.is_date',
					'validation' => 'required|max:255',
					'class' 	 => [
						'group'  => 'col-6',
						'input'  => '',
					],
					'label' 	 => 'Add Date and Time fields to this item',
					'type'		 => 'select',
					'options'	 => [
						BookableItemService::$IS_DATE_NO_DATE => 'No date/time',
						BookableItemService::$IS_DATE_ADD_DATE_ONLY => 'Add date only (unrestricted)',
						BookableItemService::$IS_DATE_ADD_DATE_AND_TIME => 'Add date and time (unrestricted)',
						BookableItemService::$IS_DATE_ADD_DATE_AND_TIME_RESTRICTED => 'Add date and time (restricted)',
						BookableItemService::$IS_DATE_ADD_DATE_RESTRICTED => 'Add date only (restricted)',
						BookableItemService::$IS_DATE_ADD_TIMESLOT => 'Add timeslot selection',
					],
					'value' => BookableItemService::$IS_DATE_NO_DATE,
					'conditions' => [
						[
							'fields' => 'date_field_name',
							'if_value' => 'is:'.BookableItemService::$IS_DATE_ADD_DATE_ONLY
						],
						// [
						// 	'fields' => 'timeslot_from',
						// 	'if_value' => 'is:3'
						// ],
						// [
						// 	'fields' => 'timeslot_to',
						// 	'if_value' => 'is:3'
						// ],
						[
							'fields' => 'office_hours',
							'if_value' => 'or:'.BookableItemService::$IS_DATE_ADD_DATE_AND_TIME_RESTRICTED.','.BookableItemService::$IS_DATE_ADD_TIMESLOT.','.BookableItemService::$IS_DATE_ADD_DATE_RESTRICTED
						],
						[
							'fields' => 'session_length',
							'if_value' => 'is:'.BookableItemService::$IS_DATE_ADD_TIMESLOT
						],
						[
							'fields' => 'booking_gap_time',
							'if_value' => 'is:'.BookableItemService::$IS_DATE_ADD_TIMESLOT
						]
					],
				],
				'date_field_name' => [
					'object_key' => 'service.date_field_name',
					'validation' => 'max:255',
					'class' 	 => [
						'group'  => 'col-6',
						'input'  => '',
					],
					'label' 	 => 'Field Title',
					'description' => 'for example: Pickup Date',
					'type'		 => 'text',
				],
				'session_length' => [
					'object_key' => 'service.session_length',
					// 'validation' => 'minvalue:0',
					// 'class' 	 => [
					// 	'group'  => 'col-3',
					// 	'input'  => '',
					// ],
					// 'label' 	 => 'Session Length',
					// 'description' => 'Hours',
					// 'type'		 => 'number',
					// 'value'		=> '',
					// 'other_attr' => [
					// 	'step'		=> '0.5',
					// ],
					'validation' => '',
					'class' 	 => [
						'group'  => 'col-3',
						'input'  => '',
					],
					'label'	=> 'Session Length',
					'type'  => 'time24',
					'value' => '00:00'
				],
				'booking_gap_time' => [
					'object_key' => 'service.booking_gap_time',
					'validation' => 'minvalue:0',
					'class' 	 => [
						'group'  => 'col-3',
						'input'  => '',
					],
					'label' 	 => 'Booking Gap Time',
					'description' => 'Minute',
					'type'		 => 'number',
					'value'		=> '0',
					'other_attr' => [
						'step'		=> '10',
					],
				],
				// 'timeslot_from' => [
				// 	'object_key' => 'service.timeslot_from',
				// 	'validation' => 'max:255',
				// 	'class' 	 => [
				// 		'group'  => 'col-3',
				// 		'input'  => '',
				// 	],
				// 	'label' 	 => 'From',
				// 	'description' => '',
				// 	'type'		 => 'select',
				// 	'options'	 => BookableItemService::$HOURS_24h_ARR,
				// ],
				// 'timeslot_to' => [
				// 	'object_key' => 'service.timeslot_to',
				// 	'validation' => 'max:255',
				// 	'class' 	 => [
				// 		'group'  => 'col-3',
				// 		'input'  => '',
				// 	],
				// 	'label' 	 => 'To',
				// 	'description' => '',
				// 	'type'		 => 'select',
				// 	'options'	 => BookableItemService::$HOURS_24h_ARR,
				// ],
				'office_hours' => [
					'object_key' => 'office_hours',
					'validation' => '',
					'class' 	 => [
						'group'  => 'col-12 mt-2',
						'input'  => '',
					],
					'label'	=> 'Timeslot Selection',
					'type'  => 'office-hours',
					'tooltip' => 'Following day or weekend bookings must made within this time range.'
				],
				'assign_to_user_id' => [
					'object_key' => 'service.assign_to_user_id',
					'validation' => 'required',
					'class' 	 => [
						'group' => 'col-12',
						'input' => '',
					],
					'label' 	 => 'Assign to 3rd-Party Users',
					'description' => '',
					'type'		 => 'select',
					'options'	 => $party_access,
					'value' => null,
				],
				'hide_cart_functionality' => [
					'object_key' => 'service.hide_cart_functionality',
					'validation' => '',
					'class' 	 => [
						'group'  => 'checkbox col-4 mt-3',
						'input'  => '',
					],
					'label' 	 => '',
					'type'		 => 'checkbox',
					'options'	 => [
						'1' => 'Hide Cart functionality'
					],
					// 'hidden_on'	 => 'edit'
				],
				'payment_to_aria' => [
					'object_key' => 'service.payment_to_aria',
					'validation' => '',
					'class' 	 => [
						'group'  => 'checkbox col-4 mt-3',
						'input'  => '',
					],
					'label' 	 => '',
					'type'		 => 'checkbox',
					'options'	 => [
						BookableItemService::$PAYMENT_TO_ARIA_YES => 'Payment to Aria'
					],
					// 'hidden_on'	 => 'edit'
				],
				'hide_pricing' => [
					'object_key' => 'service.hide_pricing',
					'validation' => '',
					'class' 	 => [
						'group'  => 'checkbox col-4 mt-3',
						'input'  => '',
					],
					'label' 	 => '',
					'type'		 => 'checkbox',
					'options'	 => [
						BookableItemService::$HIDE_PRICING_YES => 'Hide Pricing'
					],
					// 'hidden_on'	 => 'edit'
				],
				'bond_amount' => [
					'object_key' => 'service.bond_amount',
					'validation' => 'min:0',
					'class' 	 => [
						'group'  => '_price col',
						'input'  => '',
					],
					'label'	=> 'Bond amount',
					'description' => 'Leave empty when no bond required',
					'type'  => 'number',
				],
			'row_end',

			// Line item builder
			// 'title:Items',
			// 'line_items' => [
			// 	'validation' => '',
			// 	'class' 	 => [
			// 		'group'  => '',
			// 		'input'  => '',
			// 	],
			// 	'label'	=> 'Line items',
			// 	'type'  => 'line-items',
			// ],

			// Booking policy
			// 'title:Booking policy',
			'row_start',
				'enable_booking_policy' => [
					'object_key' => 'enable_booking_policy',
					'validation' => '',
					'class' 	 => [
						'group'  => 'checkbox col-12 mt-3',
						'input'  => '',
					],
					'label' 	 => '',
					'type'		 => 'checkbox',
					'options'	 => [
						self::$ENABLE_BOOKING_POLICY_ON => 'Booking policy'
					],
					'value' => self::$ENABLE_BOOKING_POLICY_ON,
					'conditions' => [
						[
							'fields' => 'prior_to_book_hours',
							'if_value' => 'is:1'
						],
						[
							'fields' => 'cancellation_cut_off',
							'if_value' => 'is:1'
						],
					],
				],
				'prior_to_book_hours' => [
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label' 	 => 'Residents can make bookings up to:',
					'description' => 'before start time.',
					'type'		 => 'select',
					'options' => [
						'1' => '1 hr',
						'2' => '2 hrs',
						'3' => '3 hrs',
						'4' => '4 hrs',
						'6' => '6 hrs',
						'8' => '8 hrs',
						'12' => '12 hrs',
						'24' => '24 hrs',
						'48' => '48 hrs',
					]
				],
				'cancellation_cut_off' => [
					'validation' => 'required|min:0',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label' 	 => 'Bookings can be changed or cancelled, up to:',
					'description' => 'hours before their booking.',
					'type'		 => 'number',
					'value'		 => 24
				],
			'row_end'
		]);
	}


	/**
	 * Item type: HIRE fields 
	 * 
	 **/ 
	public static function form_hire_fields() {

		return array_merge(self::form_fields(), [

			// Booking Admin
			'title: Admin',
			'row_start',
			'is_free_as_admin' => [
				'validation' => '',
				'class' 	 => [
					'group'  => 'checkbox mb-4 mt-3 col-12',
					'input'  => '',
				],
				'label' 	 => '',
				'type'		 => 'checkbox',
				'options'	 => [
					self::$TYPE_HIRE => 'Book As Admin'
				],
				// 'hidden_on'	 => 'edit'
			],
			'row_end',
			
			// Stock
			'title:Stock Options',
			'row_start',
				// Available QTY
				'available_qty' => [
					'object_key' => 'hire.available_qty',
					'validation' => 'min:0',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label'	=> 'Available stock',
					'type'  => 'number',
					'value' => 1,
				],
				'allow_multiple' => [
					'object_key' => 'hire.allow_multiple',
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label'	=> 'Allow multiple QTY',
					'type'  => 'select',
					'options' => [
						'1' => 'Allow',
						'0' => 'Do not allow'
					]
				],
				'allow_multiple_max' => [
					'object_key' => 'hire.allow_multiple_max',
					'validation' => '',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label'	=> 'Set maximum limit?',
					'type'  => 'number',
				],
			'row_end',

			// Pricing
			'title:Item Pricing',
			'row_start',
				'item_price' => [
					'object_key' => 'hire.item_price',
					'validation' => 'required|min:0',
					'class' 	 => [
						'group'  => '_price col',
						'input'  => '',
					],
					'label'	=> 'Price of hire',
					'description' => 'Enter 0 for free items',
					'type'  => 'number',
					'value' => 0
				],
				'item_price_unit' => [
					'object_key' => 'hire.item_price_unit',
					'validation' => 'required|in:day,hour',
					'class' 	 => [
						'group'  => '_price col',
						'input'  => '',
					],
					'label'	=> 'Price Unit',
					'type'  => 'select',
					'options' => [
						'day' => 'Day',
						'hour' => 'Hour'
					]
				],
				'bond_amount' => [
					'object_key' => 'hire.bond_amount',
					'validation' => 'min:0',
					'class' 	 => [
						'group'  => '_price col',
						'input'  => '',
					],
					'label'	=> 'Bond amount',
					'description' => 'Leave empty when no bond required',
					'type'  => 'number',
				],
			'row_end',

			// Date & Time validations
			'title:Booking options',
			'row_start',
				'booking_min_length' => [
					'object_key' => 'hire.booking_min_length',
					'validation' => 'required|min:0',
					'class' 	 => [
						'group'  => '_hours col',
						'input'  => '',
					],
					'label'	=> 'Min. booking length',
					'description' => 'in hours',
					'type'  => 'number',
					'value' => 1
				],
				'booking_max_length' => [
					'object_key' => 'hire.booking_max_length',
					'validation' => 'required|min:0',
					'class' 	 => [
						'group'  => '_hours col',
						'input'  => '',
					],
					'label'	=> 'Max. booking length',
					'description' => 'in hours',
					'type'  => 'number',
					'value' => 6
				],
				'allow_multiday' => [
					'object_key' => 'hire.allow_multiday',
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label'	=> 'Allow multiday?',
					'type'  => 'select',
					'options' => [
						'1' => 'Allow',
						'0' => 'Do not allow'
					],
					'value' => 1
				],
				'booking_gap' => [
					'object_key' => 'hire.booking_gap',
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label'	=> 'Gap between bookings',
					'type'  => 'select',
					'options' => [
						0 => 'No Gaps',
						5 => '5 min',
						10 => '10 min',
						15 => '15 min',
						30 => '30 min',
						45 => '45 min',
						60 => '1 hr',
						120 => '2 hrs',
						240 => '4 hrs',
					],
					'value' => 30
				],
			'row_end',

			// Booking policy
			'title:Booking policy',
			'row_start',
				'prior_to_book_hours' => [
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label' 	 => 'Residents can make bookings up to:',
					'description' => 'before start time.',
					'type'		 => 'select',
					'options' => [
						'1' => '1 hr',
						'2' => '2 hrs',
						'3' => '3 hrs',
						'4' => '4 hrs',
						'6' => '6 hrs',
						'8' => '8 hrs',
						'12' => '12 hrs',
						'24' => '24 hrs',
						'48' => '48 hrs',
					]
				],
				'cancellation_cut_off' => [
					'validation' => 'required|min:0',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label' 	 => 'Bookings can be changed or cancelled, up to:',
					'description' => 'hours before their booking.',
					'type'		 => 'number',
					'value'		 => 24
				],
			'row_end'
		]);
	}


	/**
	 * Item type: ROOM fields 
	 * 
	 **/
	public static function form_room_fields() { 
		
		return array_merge(self::form_fields(), [
			// Booking Admin
			'title: Admin',
			'row_start',
			'is_free_as_admin' => [
				'validation' => '',
				'class' 	 => [
					'group'  => 'checkbox mb-4 mt-3 col-12',
					'input'  => '',
				],
				'label' 	 => '',
				'type'		 => 'checkbox',
				'options'	 => [
					self::$TYPE_ROOM => 'Book As Admin'
				],
				// 'hidden_on'	 => 'edit'
			],
			'row_end',

			// Item Visibility
			'title: Item Visibility',
			'is_private' => [
				'validation' => 'required',
				'class' 	 => [
					'group'  => '',
					'input'  => '',
				],
				'label' => '',
				'description' => 'Private items are only bookable through the admin area. They will be hidden in the residents interface.',
				'type'		 => 'select',
				'options'	 => [
					0 => 'Public',
					1 => 'Private',
				],
			],

			// Booking Fee
			'title: Booking Fee',
			'row_start',
			'admin_fee' => [
				'validation' => 'min:0',
				'class' 	 => [
					'group'  => 'col-4',
					'input'  => '',
				],
				'label' => 'Admin Fee',
				'description' => 'Leave empty when there is no fee',
				'type'		 => 'number',
			],
			'row_end',

			'row_start',
			'add_more_fee' => [
				'validation' => 'min:0',
				'class' 	 => [
					'group'  => 'col-4',
					'input'  => '',
				],
				'label' => 'Admin Fee',
				'description' => 'Leave empty when there is no fee',
				'type'		 => 'add-more-fee',
			],
			'row_end',

			// Booking options
			'title:Booking Options',
			'row_start',
				'daily_booking_limit' => [
					'object_key' => 'room.daily_booking_limit',
					'validation' => 'min:0|max:50',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label'	=> 'Daily booking limit',
					'description' => 'Leave empty if no limit',
					'type'  => 'number',
				],
				'booking_from_time' => [
					'object_key' => 'room.booking_from_time',
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label'	=> 'Available for booking from',
					'type'  => 'time',
					'value' => '7:00'
				],
				'booking_to_time' => [
					'object_key' => 'room.booking_to_time',
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label'	=> 'Available for booking until',
					'type'  => 'time',
					'value' => '20:00'
				],
			'row_end',
			'row_start',
				'booking_min_length' => [
					'object_key' => 'room.booking_min_length',
					'validation' => 'required|min:0',
					'class' 	 => [
						'group'  => '_hours col',
						'input'  => '',
					],
					'label'	=> 'Min. booking length',
					'description' => 'in hours',
					'type'  => 'number',
					'value' => 1
				],
				'booking_max_length' => [
					'object_key' => 'room.booking_max_length',
					'validation' => 'required|min:0',
					'class' 	 => [
						'group'  => '_hours col',
						'input'  => '',
					],
					'label'	=> 'Max. booking length',
					'description' => 'in hours',
					'type'  => 'number',
					'value' => 6
				],
				'allow_multiday' => [
					'object_key' => 'room.allow_multiday',
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label'	=> 'Allow multiday?',
					'type'  => 'select',
					'options' => [
						'1' => 'Allow',
						'0' => 'Do not allow'
					],
					'value' => 0
				],
				'booking_gap' => [
					'object_key' => 'room.booking_gap',
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label'	=> 'Gap between bookings',
					'type'  => 'select',
					'options' => [
						0 => 'No Gaps',
						5 => '5 min',
						10 => '10 min',
						15 => '15 min',
						30 => '30 min',
						45 => '45 min',
						60 => '1 hr',
						120 => '2 hrs',
						240 => '4 hrs',
					],
					'value' => 30
				],
			'row_end',
			'row_start',
				'maximum_number_of_bookings_per_day' => [
					'object_key' => 'room.maximum_number_of_bookings_per_day',
					'validation' => 'min:0|max:5',
					'class' 	 => [
						'group'  => 'col-6',
						'input'  => '',
					],
					'label'	=> 'Maximum number of bookings per day',
					'description' => 'Leave empty if no limit',
					'type'  => 'number',
				],
			'row_end',

			// Booking policy
			'title:Booking policy',
			'row_start',
				'prior_to_book_hours' => [
					'validation' => 'required',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label' 	 => 'Residents can make bookings up to:',
					'description' => 'before start time.',
					'type'		 => 'select',
					'options' => [
						'1' => '1 hr',
						'2' => '2 hrs',
						'3' => '3 hrs',
						'4' => '4 hrs',
						'6' => '6 hrs',
						'8' => '8 hrs',
						'12' => '12 hrs',
						'24' => '24 hrs',
						'48' => '48 hrs',
					]
				],
				'cancellation_cut_off' => [
					'validation' => 'required|min:0',
					'class' 	 => [
						'group'  => 'col',
						'input'  => '',
					],
					'label' 	 => 'Bookings can be changed or cancelled, up to:',
					'description' => 'hours before their booking.',
					'type'		 => 'number',
					'value'		 => 24
				],
			'row_end',
		]);
	}


	/**
	 * Office Hours and options
	 * 
	 */
	public static $office_hours_fields = [
		// office_hours
		'row_start',
			'div_start|class:col-md-12',
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
     * Get the category of this item
     * @return App\Models\Category
     */
    public function category()
    {
        return $this->belongsTo('App\Models\Category');
	}
	
    /**
     * Bookable item type: Room
     * @return App\Models\BookableItem\BookableItemRoom
     */
    public function room()
    {
        return $this->hasOne('App\Models\BookableItem\BookableItemRoom');
    }

    /**
     * Bookable item type: Hire
     * @return App\Models\BookableItem\BookableItemHire
     */
    public function hire()
    {
        return $this->hasOne('App\Models\BookableItem\BookableItemHire');
    }

    /**
     * Bookable item type: Event
     * @return App\Models\BookableItem\BookableItemEvent
     */
    public function event()
    {
        return $this->hasOne('App\Models\BookableItem\BookableItemEvent');
	}

	/**
     * Bookable item type: Service
     * @return App\Models\BookableItem\BookableItemService
     */
    public function service()
    {
        return $this->hasOne('App\Models\BookableItem\BookableItemService');
	}
	
	/**
     * Get the recurring setting for this event item
     * @return App\Models\RecurringEvent
     */
    public function recurring()
    {
        return $this->hasOne('App\Models\RecurringEvent');
    }

    /**
	 * Building
	 * @return App\Models\Building
	 */
	public function building() {
		return $this->belongsTo('App\Models\Building');
	}

	/**
	 * Comments
	 * @return App\Models\Comment
	 */
	public function comments() {
		return $this->hasMany('App\Models\Comment', 'bookable_item_id', 'id');
	}

	/**
	 * Get the Line items associated with this service item
	 * @return App\Models\LineItem
	 */
	public function line_items() {
		return $this->hasMany('App\Models\LineItem', 'item_id', 'id');
	}

	/**
	 * Get the stored Cart state for this item for the logged in user 
	 * @return App\Models\Cart
	 */
	public function cart() {
		return $this->hasOne('App\Models\Cart', 'item_id', 'id')->where('user_id', Auth::id());
    }

	
	/**
	 * BookableItemFee
	 * @return App\Models\Comment
	 */
	public function bookableItemFees() {
		return $this->hasMany('App\Models\BookableItemFee', 'bookable_item_id', 'id');
	}



	/***********************************************************************/
	/***************************  LOCAL SCOPES  ****************************/
	/***********************************************************************/

	/**
	 * by type: Room
	 */
	function scopeTypeRoom($query) {
		return $query->where('type', self::$TYPE_ROOM);
	}

	/**
	 * by type: Event
	 */
	function scopeTypeEvent($query) {
		return $query->where('type', self::$TYPE_EVENT);
	}

	/**
	 *  by type: Hire
	 */
	function scopeTypeHire($query) {
		return $query->where('type', self::$TYPE_HIRE);
	}

	/**
	 *  by type: Service
	 */
	function scopeTypeService($query) {
		return $query->where('type', self::$TYPE_SERVICE);
	}

	/** 
	 * Get Bookable items only form the logged in user's building
	 */
	function scopeMyItems($query, $assign_to_user_id =  false) {

		$user = Auth::user();

		//  Admins: Can see all bookings
		if( $user->isSuperAdmin() || $user->isAdmin() ) {
			return $query;
		}

		if($user->isExternal() && $assign_to_user_id) {
			return $query->where('bookable_item_service.assign_to_user_id', $user->id);
		}

		// Admin: todo 

		// Own only: Building Manager, Staff
		return $query->whereRaw("bookable_items.building_id IN (SELECT GROUP_CONCAT(DISTINCT(building_id)) as building_ids FROM building_user WHERE user_id = {$user->id} AND relation_status = 1)");
	}
	



	/***********************************************************************/
	/*************************** PUBLIC METHODS  ***************************/
	/***********************************************************************/

	/**
	 * @return bool
	 */
	public function isFree() {
		return $this->is_free > 0;
	}

	/**
	 * @return bool
	 */
	public function isFreeAsAdmin() {
		if (Auth::check()) {
			//there is a user logged in, now to get the id
			$is_admin = (Auth::user()->isAdmin() || Auth::user()->isSuperAdmin());
			return ($this->is_free_as_admin > 0 && $is_admin) ? true : false;
		}
		return false;
	}

	/**
	 * @return bool
	 */
	public function isService() {
		return $this->type == self::$TYPE_SERVICE ? true : false;
	}

	/**
	 * @return bool
	 */
	public function isEvent() {
		return $this->type == self::$TYPE_EVENT ? true : false;
	}

	/**
	 * @return bool
	 */
	public function isHire() {
		return $this->type == self::$TYPE_HIRE ? true : false;
	}

	/**
	 * @return bool
	 */
	public function isRoom() {
		return $this->type == self::$TYPE_ROOM ? true : false;
	}

	/**
	 * @return bool
	 */
	public function isPaymentToAria() {
		if (Auth::check()) {
			return $this->isService() && $this->service->payment_to_aria == BookableItemService::$PAYMENT_TO_ARIA_YES;
		}
		return false;
	}



	/******** File Management ********/

	/**
	 * Return with the card image path
	 */
	public function imagePath() {
		return '/items/'.$this->id .'/';
	}

	/**
	 * Return with the gallery images path
	 */
	public function galleryPath() {
		return '/items/'.$this->id .'/gallery';
	}

	/**
	 * Return with the terms attachments path
	 */
	public function termsPath() {
		return '/items/'.$this->id .'/terms';
	}

	/**
	 * Get the images for the gallery
	 * @return array image urls
	 */
	public function getGalleryImages() {
		
		$images = Storage::disk('public')->allFiles($this->galleryPath());

		if(!$images) return [];

		return array_filter($images, function($img) {
			return strpos($img, ".jpg") !== false && strpos($img, "_thumb") === false;
		});
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
	 * Get the terms of this item
	 */
	public function getTerms() {

		$terms = Storage::disk('public')->allFiles($this->termsPath());
		
		if(!$terms) return [];

		return array_filter($terms, function($term) {
			return strpos($term, ".pdf") !== false;
		});
	}

	/**
	 * Get the bookable items PDF terms
	 * @return 
	 */
	public function getPDFTerms() {

		$files = Storage::disk('public')->allFiles($this->termsPath()); 

		if(!$files) return '';
		
		$files = array_filter($files, function($file) {
			return strpos($file, ".pdf") !== false;
		});

		$files_arr = [];
		$i = 0;
		
		foreach($files as $file) {
			$files_arr[$i] = pathinfo($file);
			$files_arr[$i]['filename'] = str_replace('-', ' ', $files_arr[$i]['filename']);
			$files_arr[$i]['filename'] = ucwords(preg_replace('/^(.*?)___/', '', $files_arr[$i]['filename']));
	
			$files_arr[$i]['path'] = Storage::url('items/'.$this->id.'/terms/'.$files_arr[$i]['basename']);
			$files_arr[$i]['file'] = $file;
			$i++;
		}

		return json_decode(json_encode($files_arr)); 
	}
	
	/**
	 * Get the thumbnail
	 * 
	 * @param string $size array('180x180', '820x500')
	 * @return mixed thumbnail_url || false
	 */
	public function getThumb($size = '180x180') {
		if( !in_array($size, ['180x180', '820x500']) ) {
			$size = '180x180';
		}
		return $this->is_thumb ? Storage::disk('public')->url('items/'.$this->id.'/'.$this->is_thumb.'_'.$size.'.jpg') : false;
	}

	public function getThumbWithoutDomain($size = '180x180') {

		if( !in_array($size, ['180x180', '820x500']) ) {
			$size = '180x180';
		}
		return $this->is_thumb ? 'storage/items/'.$this->id.'/'.$this->is_thumb.'_'.$size.'.jpg' : false;
	}
	
	public static function getThumbStatic($is_thumb, $id, $size = '180x180') {
		if( !in_array($size, ['180x180', '820x500']) ) {
			$size = '180x180';
		}
		return $is_thumb ? Storage::disk('public')->url('items/'.$id.'/'.$is_thumb.'_'.$size.'.jpg') : false;
	}






	
	/**
	 * Get the type in string format
	 * @return str type string
	 */
	public function typeStr() {
		if(!$this->type || !in_array($this->type, [1,2,3,4]) ) {
			return '';
		}
		return self::$TYPE_LABEL[$this->type];
	}


	/**
	 * Get the item type label
	 */
	public function getTypeLabel() {

		switch($this->type) {
			case 1:
				return 'Room/Areas';
				break;
			case 2:
				return 'Hire';
				break;
			case 3:
				return 'Event';
				break;
			case 4:
				return 'Service';
				break;
		}
		return '';
	}


	/**
	 * Get the Status label
	 */
	public function getStatus($is_label = false) {

		switch($this->status) {

			case self::$STATUS_DRAFT:
				return $is_label ? '<span class="label l-gray">Draft</span>' : 'Draft';
				break;

			case self::$STATUS_ACTIVE:
				return $is_label ? '<span class="label l-green">Active</span>' : 'Active';
				break;

			case self::$STATUS_CANCELLED:
				return $is_label ? '<span class="label l-red">Cancelled</span>' : 'Cancelled';
				break;

			case self::$STATUS_ARCHIVE:
				return $is_label ? '<span class="label l-gray">Archive</span>' : 'Archive';
				break;

		}
		return '';
	}


	/**
	 * Get the Visibility Status Label
	 * @return string
	 */
	public function getVisibility() {
		return $this->is_private ? 'Private' : 'Public';
	}


	/**
	 * Format ghe price tag from JSON
	 * 
	 * @param bool $is_html 
	 * @return str html price tag
	 */
	public function getPriceTag($is_html = false) {

		if(!$this->price_tag) {
			return '';
		}

		$tag = json_decode($this->price_tag);

		if($tag->tag == 'from') {
			return $is_html ? 
				'<span class="unit">from</span><span class="price">'.priceFormat($tag->price, 0).'</span>' : 
				'from '.priceFormat($tag->price, 0);
		}

		return $is_html ? 
			'<span class="price">'.priceFormat($tag->price, 0).'</span><span class="unit">'.$tag->tag.'</span>' : 
			priceFormat($tag->price, 0).' '.$tag->tag;
	}


	/**
	 * for Service items: Calculate the total of all items in Cart
	 * 
	 * @param array $cart_items
	 * @return decimal $subtotal
	 */
	public function calculateSubTotal($cart_items) {

		if( !$cart_items) {
			return 0;
		}

		$subtotal = 0;

		foreach($cart_items as $cart_item) {
			// this item has a single price
			$item_price = isset($cart_item->item->price) ? $cart_item->item->price : 0;
			$subtotal += ($item_price * $cart_item->qty);
		}

		return $subtotal;
	}


	/**
	 * Get the Cancellation Cut off Date
	 * 
	 * @param date $booking_start
	 * @return Carbon $cancellation_cut_off_date_time
	 */
	public function getCutOffDate($booking_start) {

		if(!$booking_start) {
			return null;
		}

		if(!$this->cancellation_cut_off) {
			$this->cancellation_cut_off = 24;
		}

		$cut_off = Carbon::parse($booking_start)->addHours( - $this->cancellation_cut_off);

		if( $cut_off <= Carbon::now() ) {
			return Carbon::now();
		}

		return $cut_off;
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
		return $comment->get_comments(['bookable_item_id' => $this->id], $offset, $limit);		
	}
	
	/***********************************************************************/
	/************************ PUBLIC STATIC METHODS ************************/
	/***********************************************************************/


	/**
	 * Get the item type_ID from string
	 */
	public static function getTypeID($type) {

		switch($type) {
			//
			case 'room':
				return self::$TYPE_ROOM;
				break;
			//
			case 'hire':
				return self::$TYPE_HIRE;
				break;
			//
			case 'service':
				return self::$TYPE_SERVICE;
				break;
			//
			case 'event':
				return self::$TYPE_EVENT;
				break;
		}
		return '';
	}


	/**
	 * Get Event type for an event booking
	 */
	public static function getEventType($type_id) {

		if(!$type_id) {
			return "";
		}

		switch($type_id) {

			case BookableItemEvent::$TYPE_SINGLE:
				return 'Single';
				break;

			case BookableItemEvent::$TYPE_REPEATING:
				return 'Repeating';
				break;
				
			default: 
				return '';
				break;
		}
	}


	

	/***********************************************************************/
	/******************** CALENDAR RELATED VALIDATIONS *********************/
	/***********************************************************************/


	/**
	 * Get the bookings in a provided date range
	 * 
	 * @param array $dateRange
	 * @param array $fields
	 * @param int $exclude_id
	 * 
	 * @return App\Models\Booking
	 */
	public function getBookings($dateRange = [], $fields = '', $exclude_id = '') {

		$from = Carbon::parse($dateRange[0].' 00:00:00');
		$to   = Carbon::parse($dateRange[1].' 24:00:00');

		$Bookings = Booking::where(function ($query) use($from, $to, $exclude_id) {
				
				$query->where([
					'bookable_item_id' => $this->id,
					'building_id' => $this->building_id
				])
				->whereBetween('start', [$from, $to]);
				if($exclude_id) {
					$query->where('id', '!=', $exclude_id);
				}

			})->orWhere(function($query) use($from, $to, $exclude_id) {
				
				$query->where([
					'bookable_item_id' => $this->id,
					'building_id' => $this->building_id
				])
				->whereBetween('end', [$from, $to]);
				if($exclude_id) {
					$query->where('id', '!=', $exclude_id);
				}
			})

			//
			->orderBy('start', 'ASC')
			->get($fields);

		return $Bookings;
	}


	/**
	 * Get the Events that are associated with this BookableItem
	 * @return App\Models\BookableItem
	 */
	public function getEvents($dateRange = []) {

		$Events = self::select(DB::raw("
				id, 
				CONCAT(bookable_item_event.event_date, ' ', bookable_item_event.event_from) AS start,
				CONCAT(bookable_item_event.event_date, ' ', bookable_item_event.event_to) AS end
			"))
			->leftJoin('bookable_item_event', 'bookable_items.id', '=', 'bookable_item_event.bookable_item_id')
			->where([
				'bookable_item_event.location_id' => $this->id, // Event location is this Room/Area 
				'type' => self::$TYPE_EVENT // only events
			]);

		if($dateRange) {
			$Events->whereBetween('bookable_item_event.event_date', $dateRange);
		}

		return $Events->orderBy('event_from', 'ASC')->get();
	}


	/**
	 * Get the Unavailable Dates in a provided date-range.
	 * 
	 *  - validate max booking limit
	 *  - validate office hours
	 *  - validate prior to book (in hours)
	 * 
	 * @param str - from
	 * @param str - to
	 * @param int - exclude_booking_id
	 * 
	 * @return array disabled dates
	 */
	public function getUnavailableDates($from, $to, $exclude_booking_id = '') {

		// This validation is only for Hire and Room/Area bookings
		if(! in_array($this->type, [self::$TYPE_ROOM, self::$TYPE_HIRE]) ) {
			return null;
		}

		$disabled_dates = [];

		// Get bookings in date-range
		$bookings = $this->getBookings([$from, $to], ['id', 'start', 'end', 'user_id'], $exclude_booking_id);

		// for Room/Area bookings also add events that are booked into this room
		if($this->type == self::$TYPE_ROOM) {
			// Get events in date-range
			$events = $this->getEvents([$from, $to]);
			$bookings = $bookings->merge($events);
		}

		/**
		 * @return
		 * 	- booked_dates
		 * 	- fully_booked_dates
		 * 	- low_availability_dates
		 */
		$BookingDates = $this->_createBookingDatesArray($bookings, 'dates');

		// Add the booked out dates to the disabled array
		if($BookingDates['fully_booked_dates']) {
			$disabled_dates[] = $BookingDates['fully_booked_dates'];
		}
		
		$now = Carbon::now();

		/**
		 * 1. Validate: Office Hours
		 * 
		 */
		$office_hours = $this->office_hours ? $this->office_hours : $this->building->office_hours;
		$office_hours = json_decode($office_hours);
		// dd($office_hours);
		$disabled_date_by_office_hours = [];
		$disabled_date_by_office_hours_count = 1;
		foreach ($office_hours as $key => $oh) {
			if((int) $oh->status == 0) {
				$disabled_date_by_office_hours[] = $disabled_date_by_office_hours_count == 7 ? 0 : $disabled_date_by_office_hours_count;
			}
			$disabled_date_by_office_hours_count ++;
		}

		if($office_hours && $this->ignore_office_hours == false) {

			// Grab today's setting
			$today = $office_hours->{$now->format('l')};

			// Office closed today
			if($today->status == false) {
				$disabled_dates[] = $now->format('Y-m-d');
			}
			// Office is Open today
			else {
				/**
				 * Booking can be made until to office closes
				 * Add 30 min security gap to make sure no last minute bookings are made.
				 **/
				if(Carbon::now()->addMinutes(30) >= Carbon::parse($today->to)) {
					$disabled_dates[] = $now->format('Y-m-d');
				}
			}

			/**
			 * also add all other days until the next Office Open day.
			 * So for example if the current day is Saturday and the office is closed for the weekend, prevent residents to create bookings for the following Sunday.
			 **/ 
			$ocd = $this->_getClosedDates($now->format('N'), $office_hours);
			if($ocd) {
				array_push($disabled_dates, $ocd);
			}
		}

			
		/** 
		 * 2. Validate: Prior to booking limit
		 * 
		 * */
		$min_length_of_booking = $this->room ? $this->room->booking_min_length : $this->hire->booking_min_length;
		$earliest_booking_from_now = $now->addHours($this->prior_to_book_hours)->ceilMinutes('15');

		$period = CarbonPeriod::create(Carbon::now()->format('Y-m-d'), $earliest_booking_from_now->format('Y-m-d'));

		if($period) {
			foreach ($period as $date_key) {

				$date_key = $date_key->format('Y-m-d');

				// Get the time until the booking can be made on this date
				if($this->type == self::$TYPE_ROOM) {
					// Room
					$booking_to_time = $this->room->booking_to_time;
				}
				else {
					// Hire		
					$booking_to_time = "24:00:00"; 

					if($this->ignore_office_hours == false) {			
						$day_hours = $office_hours->{Carbon::parse($date_key)->format('l')};
						$booking_to_time = Carbon::parse($day_hours->to)->format('H:i:s');
					}
				}

				$can_book_until = Carbon::parse($date_key.' '.$booking_to_time)->copy()->addHours(-$min_length_of_booking);

				if($earliest_booking_from_now >= $can_book_until) {
					$disabled_dates[] = $date_key;
				}

			}
		}

		return [
			'bookings' => $BookingDates['booked_dates'],
			'unavailable' => array_unique(Arr::flatten($disabled_dates)),
			'low_availability' => $BookingDates['low_availability_dates'],
			'disabled_for_full_range' => $BookingDates['disabled_for_full_range'],
			'disabled_duplicate_book_same_date_by_resident' => $BookingDates['disabled_duplicate_book_same_date_by_resident'],
			'disabled_date_by_office_hours' => $disabled_date_by_office_hours
		];
	}


	/**
	 * Get the unavailable time slots for a specific date or date-range
	 */
	public function getAvailableTimes($request) {

		$request = (Object) $request;

		$from = Carbon::parse($request->start)->format('Y-m-d');
		$to	  = Carbon::parse($request->end)->format('Y-m-d');

		// Get bookings in date-range
		$bookings = $this->getBookings([$from, $to], ['id', 'start', 'end'], $request->exclude_booking_id);

		// for Room/Area bookings also add events that are booked into this room
		if($this->type == self::$TYPE_ROOM) {
			// Get events in date-range
			$events = $this->getEvents([$from, $to]);
			$bookings = $bookings->merge($events);
		}

		/**
		 * @return
		 *	- times (all booked out time slots)
		 *	- time_slots (all available time slots)
		 *  - time_dropdown_options (dropdown options for time pickers)
		 */
		if(in_array($this->type, [self::$TYPE_ROOM, self::$TYPE_HIRE])){
			$booked_dates = $this->_createBookingDatesArray($bookings, 'time_slots', [$from, $to]);
		}
		if($this->type == self::$TYPE_SERVICE){
			$booked_dates = $this->_createBookingDatesArrayService($bookings, $from);
		}
		return $booked_dates;
	}



	/**
	 * Validate the Dates for a booking (server side date validation)
	 * 
	 * @param string $date_str - from
	 * @param string $date_str - to
	 * @param array $times - [from, to]
	 * @param int $exclude_booking_id - booking id to exclude from the validation (for updates)
	 * 
	 * @return boolean
	 */
	public function validateDatesForBooking($start_date, $end_date, $times, $exclude_booking_id = null) {

		$unavailableDates = $this->getUnavailableDates($start_date, $end_date, $exclude_booking_id);

		$i = 0;
		$valid = 0;

		foreach([$start_date, $end_date] as $date) {

			// all good, no booking found
			if( ! isset($unavailableDates['bookings']->{$date}) ) {
				$valid++;
				$i++;
				continue;
			}

			// There are bookings, check if the from-to time is valid.

			$dateObj = $unavailableDates['bookings']->{$date};

			if($dateObj->gaps) {
				foreach($dateObj->gaps as $gap) {

					$gap = (object) $gap;

					$booking = Carbon::parse($date.' '.$times[$i]);

					if( $booking->between(Carbon::parse($gap->start), Carbon::parse($gap->end)) ) {
						$valid++;
						break;
					}
				}
			}
			$i++;
		}

		// Valid must be 2, it means the booking start/end are both valid.
		return $valid == 2 ? true : false;
	}





	/***********************************************************************/
	/*************************** PRIVATE HELPERS ***************************/
	/***********************************************************************/

	/**
	 * Convert bookings into an array where key is the date.
	 * 
	 * @param collection Bookings $bookings
	 * @param str $return_data - validation type: dates or times
	 * @param array $dateRange - the selected date-range
	 * @param int $qty - the selected qty
	 * 
	 * @return array
	 */
	private function _createBookingDatesArray($bookings, $return_data = 'dates', $dateRange = [], $qty = 1) {

		$booked_dates = $this->_organizeBookingsByDate($bookings);

		/** -- definitions: Set the validation options -- */

		// return: dates
		if($return_data == 'dates') {

			$fully_booked_dates = [];
			$low_availability_dates = [];
			$disabled_for_full_range = [];
			$disabled_duplicate_book_same_date_by_resident = [];
		}

		// Office Hours. Item or Building
		$office_hours = $this->office_hours ? $this->office_hours : $this->building->office_hours;
		$office_hours = json_decode($office_hours);
		
		// is type Room or Hire?
		$min_length_of_booking = $this->room ? $this->room->booking_min_length : $this->hire->booking_min_length;
		$max_length_of_booking = $this->room ? $this->room->booking_max_length : $this->hire->booking_max_length;

		$booking_gap = $this->room ? $this->room->booking_gap : $this->hire->booking_gap;
		$allow_multiday = $this->room ? $this->room->allow_multiday : $this->hire->allow_multiday;

		// Use this to validate hire items with qty larger than 1.
		$overlap_allowed = $this->hire ? ($this->hire->available_qty - 1) : 0;
		// just to be safe. this number cannot be smaller than 0.
		$overlap_allowed = $overlap_allowed < 0 ? 0 : $overlap_allowed;
		


		/** -- Start validations -- */


		/**
		 * Is this a multi or single day booking?
		 * - This condition will change the way we validate the time-picker options
		 */
		$is_sameDay = false;

		if($dateRange && ($dateRange[0] == $dateRange[1])) {
			$is_sameDay = true;
		}

		/**
		 * Do the validation
		 */
		foreach($booked_dates as $date_key => $date) {

			/** 
			 * Set the min/max booking times for Room bookings
			 **/
			if($this->type == self::$TYPE_ROOM) {
				$can_book_from = Carbon::parse($date_key.' '.$this->room->booking_from_time);
				$can_book_until = Carbon::parse($date_key.' '.$this->room->booking_to_time);
			}
			/** 
			 * Set the min/max booking times for Hire bookings
			 **/
			else {

				// grab this day's opening hours 
				$day_hours = $office_hours->{Carbon::parse($date_key)->format('l')};

				$can_book_from  = Carbon::parse($date_key.' '.$day_hours->from);
				$can_book_until = Carbon::parse($date_key.' '.$day_hours->to);

				// ignore office hours?
				if($this->ignore_office_hours == true) {
					$can_book_from  = Carbon::parse($date_key.' 00:00:00');
					$can_book_until = Carbon::parse($date_key.' 24:00:00');
				}
			}

			// Is this day iteration is today? If so, adjust the "can_book_from" 
			if( Carbon::parse($date_key)->isToday() ) {
				$can_book_from = Carbon::now()->ceilMinute('15');
			}

			// Check the "Prior to book" (in hours) Setting
			$earliest_booking_form_now = Carbon::now()->addHours($this->prior_to_book_hours)->ceilMinute('15');
			
			// Check if bookings still can be made on this day
			if($earliest_booking_form_now >= $can_book_until) {
				// No. There are no time left to make bookings on this date, so disable it.
				$date->gaps = null;
				$date->allow_full_range = false;
			}
			else {
		
				// Compare the "can book from" with the "earliest booking from", and use whichever is greater.
				$can_book_from = $can_book_from > $earliest_booking_form_now ? $can_book_from : $earliest_booking_form_now;
				
				// Create the testing range 
				$fromToRange = Period::make($can_book_from, $can_book_until, Precision::MINUTE, Boundaries::EXCLUDE_NONE);

				/**
				 * Validate date
				 * 
				 * - Case 1: Overlapping == false
				 * 	
				 * - Case 2: Overlapping == true
				 */

				// Overlapping not allowed, in cases when there is one item only to book. (Room bookings, or hire item where there is only one)
				if($overlap_allowed == 0) {
					// Create the periods from the bookings
					$_periods = array_map(function($time) use($date_key) {
						return Period::make($date_key.' '.$time->from, $date_key.' '.$time->to, Precision::MINUTE, Boundaries::EXCLUDE_ALL);
					}, $date->times);
					
					//
					$period_collection = new PeriodCollection(...$_periods);
					
					// Get the gaps (available time ranges)
					$gaps = $fromToRange->diff(...$period_collection);
						
					// there are available spots 
					if($gaps) {
						$g = [];
						foreach($gaps as $gap) {

							$s = $gap->getStart()->format('Y-m-d H:i:s');
							$e = $gap->getEnd()->format('Y-m-d H:i:s');
							$l = Carbon::parse($s)->diffInMinutes(Carbon::parse($e), false);

							if($l > 0) {
								$g[] = [
									'start'  => $s,
									'end' 	 => $e,
									'length' => $l
								];
							}
						}
					}
					else {
						// there are no available spots 
						$g = null;
					}

					/** add gaps to the date object */
					$date->gaps = $g;
					$date->allow_full_range = false;

				}
				// Overlapping allowed, in cases when there are multiple items than can be booked.
				else {

					// Step 0: group the not overlapping events

					$group_default = [];
					$i = 0;

					foreach($date->times as $time) {
						$group_default[] = [$time->from, $time->to];
					}
					
					$_groups = [];
					$_sorted = [];

					foreach($group_default as $key => $this_range) {

						if(in_array($key, $_sorted)) continue;

						// add
						$_groups[$key][] = $this_range;
						// sorted
						$_sorted[] = $key;

						// no need to test this with itself
						unset($group_default[$key]);

						// test
						foreach($group_default as $k => $test_range) {

							if(in_array($k, $_sorted)) continue;

							$this_range_max = max(array_column($_groups[$key], 1));
				
							if(Carbon::parse($date_key.' '.$test_range[0]) >= Carbon::parse($date_key.' '.$this_range_max)) {
								// add
								$_groups[$key][] = $test_range;
								// sorted
								$_sorted[] = $k;
							}
						}		
					}


					// There is more items available for the selected time range, skip the following validation.
					$group_no = count($_groups);

					// Validate only if all items have booking for this date.
					if( ($overlap_allowed + 1) <= $group_no ) {

						// Validate if there are free gaps left to make a booking in the selected time range.
						$Collection = [];

						foreach($_groups as $group) {
							$group_collection = new PeriodCollection();
							foreach($group as $period) { 
								$group_collection[] = Period::make($date_key.' '.$period[0], $date_key.' '.$period[1], Precision::MINUTE, Boundaries::EXCLUDE_ALL);
							}
							$Collection[] = $group_collection;
						}
						// Test the selected range with the booked out time-ranges.
						$a = new PeriodCollection($fromToRange);
						// This will return a collection of fully booked out periods.
						$booked_out_periods = $a->overlap(...$Collection);
						
						// Now, get the available gaps between the booked out periods.
						$gaps = $fromToRange->diff(...$booked_out_periods);
						
						// there are available spots 
						if($gaps) {
							$g = [];
							foreach($gaps as $gap) {

								$s = $gap->getStart()->format('Y-m-d H:i:s');
								$e = $gap->getEnd()->format('Y-m-d H:i:s');
								$l = Carbon::parse($s)->diffInMinutes(Carbon::parse($e), false);

								if($l > 0) {
									$g[] = [
										'start'  => $s,
										'end' 	 => $e,
										'length' => $l
									];
								}
							}
						}
						else {
							// there are no available spots 
							$g = null;
						}

						/** add gaps to the date object */
						$date->gaps = $g;
						$date->allow_full_range = false;
					}
					else {
						// No need to validate, so return the entire range.
						$date->gaps[] = [
							'start'  => $can_book_from,
							'end' 	 => $can_book_until,
							'length' => Carbon::parse($can_book_from)->diffInMinutes(Carbon::parse($can_book_until), false)
						];
						$date->allow_full_range = true;
					}

				}
			}


			/** -- End validations -- */

	
			/**
			 * Return for dates
			 * 
			 * 	- count the free spots
			 *  - assign low availability status to all dates where spots are less than 2.
			 */
			if($return_data == 'dates') {
				

				// Validate daily booking limit (only for Room bookings)
				if($this->type == self::$TYPE_ROOM) {
					if($this->room->daily_booking_limit && (count($date->times) >= $this->room->daily_booking_limit)) {
						$disabled_dates[] = $date_key;
					}
				}

				// Check if there are any free Spots
				$date->free_spots = 0;

				if($date->gaps) {
					foreach($date->gaps as $gap) {
						$date->free_spots += (int) ($gap['length'] / (($min_length_of_booking * 60) + $booking_gap));
					}
				}
				if($date->free_spots == 0) {
					$fully_booked_dates[] = $date_key;
				}

				
				// Mark day as "Low av" when 2 or less time slots left
				else if($date->free_spots <= 3) {
					$low_availability_dates[] = $date_key;
				}

				if($this->type == self::$TYPE_ROOM) { 
					// With room/area: We need it show when even a small booking is made for the date, it will show yellow dot
					$low_availability_dates[] = $date_key;

					// disabled duplicate book same date by resident
					if (
							Auth::user()->isResident() && 
							isset($this->room->maximum_number_of_bookings_per_day) && 
							(int) $this->room->maximum_number_of_bookings_per_day > 0 && 
							count($date->times) >= (int) $this->room->maximum_number_of_bookings_per_day
						){ // If anyone uses the Dining Room in a day, then no Residents can book it
						$disabled_duplicate_book_same_date_by_resident[] = $date_key;
					}
				}

				// block dates for range pickers where there is no full range available
				if($date->allow_full_range == false) {
					$disabled_for_full_range[] = $date_key;
				}
			}


			/**
			 * Return for time-slots
			 * 
			 *  - validate minium booking hours (remove slots that are smaller than min booking)
			 *  - add booking gaps
			 *  - create the time dropdown options for the selected date-range in 15min increasement
			 * 
			 */
			if($return_data == 'time_slots') {

				$time_dropdown_options = null;

				if($date->gaps) {

					foreach($date->gaps as $gap) {
					
						$gap = (Object) $gap;
	
						$start = Carbon::parse($gap->start);
						$end   = Carbon::parse($gap->end);

						// Add the booking gap 
						$start->addMinutes($booking_gap);
	
						// Stop the looping before End - (min booking length + booking gap)
						$stop = $end->copy()->addMinutes( - ($min_length_of_booking * 60) );
						//$stop = $end->copy()->addMinutes( - (($min_length_of_booking * 60) + $booking_gap) );

						// Adding the booking gab after the last order ? (disabled)
						$max = $end->copy(); // $max = $end->copy()->addMinutes( - $booking_gap);
					
						// if we have max length defined, calculate the max end-time for this time slot.
						$max_length_allowed = $max_length_of_booking ? $start->copy()->addHours($max_length_of_booking) : $max;

						// not enough time between, skip
						if($stop <= $start) {
							continue;
						}

						// Add first iteration
						$time_dropdown_options[] = [
							'value' => $start->toTimeString(),
							'max' 	=> $max > $max_length_allowed ? $max_length_allowed->toTimeString() : $max->toTimeString(),
							// Same Day booking ? Start + Min booking length : Start
							'min' 	=> $is_sameDay ? $start->copy()->addHours($min_length_of_booking)->toTimeString() : $start->toTimeString()
						];
	
						$_from = $start->copy();
	
						// loop: create 15min options
						for ($i = 0; $i <= 94; $i ++) {
							
							$_from->addMinutes(15);
							$max_length_allowed = $max_length_of_booking ? $_from->copy()->addHours($max_length_of_booking) : $max;

							if($stop < $_from) break;

							$time_dropdown_options[] = [
								'value' => $_from->toTimeString(),
								'max' 	=> $max > $max_length_allowed ? $max_length_allowed->toTimeString() : $max->toTimeString(),
								// Same Day booking ? Start + Min booking length : Start
								'min' 	=> $is_sameDay ? $_from->copy()->addHours($min_length_of_booking)->toTimeString() : $_from->toTimeString()
							];
	
							if($_from >= $stop) break;
						}
					}
				}

				// Assign the dropdown time options 
				$date->time_dropdown_options = $time_dropdown_options;
			}	
		}
		

		// Return for DATES
		if($return_data == 'dates') {

			return [
				'booked_dates' => $booked_dates,
				'fully_booked_dates' => array_unique($fully_booked_dates),
				'low_availability_dates' => $low_availability_dates,
				'disabled_for_full_range' => $disabled_for_full_range,
				'disabled_duplicate_book_same_date_by_resident' => $disabled_duplicate_book_same_date_by_resident,
			];
			// done.
		}

		// Return for TIME_SLOTS
		if($return_data == 'time_slots') {

			/** Get the default hours for the range start/end if they're not yet present. */ 

			// Start
			if( isset($booked_dates->{$dateRange[0]}) == false ) {
				$booked_dates->{$dateRange[0]} = $this->_getDefaultTimeRange($dateRange[0], $office_hours, $is_sameDay);
			}
			// End
			if( ($dateRange[0] != $dateRange[1]) && (isset($booked_dates->{$dateRange[1]}) == false) ) {
				$booked_dates->{$dateRange[1]} = $this->_getDefaultTimeRange($dateRange[1], $office_hours, $is_sameDay);
			}

			// Return with the booked dates
			if( $booked_dates ) {
				return $booked_dates;
				// done.
			}	
		}

	}


	/**
	 * Convert bookings into an array where key is the date.
	 * 
	 * @param collection Bookings $bookings
	 * @param str $return_data - validation type: dates or times
	 * @param array $dateRange - the selected date-range
	 * @param int $qty - the selected qty
	 * 
	 * @return array
	 */
	private function _createBookingDatesArrayService($bookings, $from) {
		// Office Hours. Item or Building
		$office_hours = $this->office_hours ? $this->office_hours : $this->building->office_hours;

		if($this->service->is_date == BookableItemService::$IS_DATE_ADD_DATE_AND_TIME) {
			$office_hours = $this->building->office_hours;
		}
		$office_hours = json_decode($office_hours);
		$day_hours = $office_hours->{Carbon::parse($from)->format('l')};
		$can_book_from  = Carbon::parse($from.' '.$day_hours->from);
		$can_book_until = Carbon::parse($from.' '.$day_hours->to);

		// Fix Today! No bookings in the past... at least not yet:)
		if($can_book_from->isToday()) {
			$can_book_from = Carbon::now()->ceilMinute('15');
		}

		// Check prior to book (in hours)
		$earliest_booking_form_now = Carbon::now()->addHours($this->prior_to_book_hours)->ceilMinute('15');

		// Compare the can book from to the earliest  booking form, and use whichever is greater.
		$can_book_from = $can_book_from > $earliest_booking_form_now ? $can_book_from : $earliest_booking_form_now;
			
		$is_timeslot = $this->service->is_date == BookableItemService::$IS_DATE_ADD_TIMESLOT;

		// now we have the from-to range, let's create the dropdown options with the 15min increasement.
		$dropdown_options = null;

		$min_length_of_booking = 1;
		$max_length_of_booking = 9;
		
		$booking_gap = $is_timeslot ? 0 : 120;


		$stop = $can_book_until->copy()->addMinutes( - (($min_length_of_booking * 60) + $booking_gap) );

		$max = $can_book_until->copy()->addMinutes( - $booking_gap);
		
		// if we have max length defined, calculate the max end-time for this time slot.
		$max_length_allowed = $max_length_of_booking ? $can_book_from->copy()->addHours($max_length_of_booking) : $max;
		

		// Add the first iteration
		$min = $can_book_from->copy();
		$_max = $max > $max_length_allowed ? $max_length_allowed : $max;	
		// $_session_length = ($this->service->session_length ?? 0) * 60; // => update 23/8/2021 to minutes instead of hour
		$_session_length = explode('.', $this->service->session_length); // section_length format decimal (0.00)
		$dropdown_options[] = [
			'value' => $can_book_from->toTimeString(),
			'max' 	=> $_max->toTimeString(),
			// 'min'	=> $is_timeslot ? $min->addHours($_session_length)->toTimeString() : $can_book_from->toTimeString()
			'min'	=> $is_timeslot ? $min->addHours($_session_length[0])->addMinutes($_session_length[1])->toTimeString() : $can_book_from->toTimeString()
		];

		// loop: create 15min options
		for ($i = 0; $i <= 94; $i ++) {
			if ($is_timeslot) {
				$booking_gap_time = $this->service->booking_gap_time ?? 0;
				$can_book_from->addMinutes($booking_gap_time);
				// $can_book_from->addHours($_session_length);
				$can_book_from->addHours($_session_length[0])->addMinutes($_session_length[1]);
			} else {
				$can_book_from->addMinutes(15);
			}
			$max_length_allowed = $max_length_of_booking ? $can_book_from->copy()->addHours($max_length_of_booking) : $max;
			$min = $can_book_from->copy();
			// $_min = $is_timeslot ? $min->addHours($_session_length) : $can_book_from;
			$_min = $is_timeslot ? $min->addHours($_session_length[0])->addMinutes($_session_length[1]) : $can_book_from;

			if($is_timeslot && $_min > $_max) break;

			$dropdown_options[] = [
				'value' => $can_book_from->toTimeString(),
				'max' 	=> $_max->toTimeString(),
				'min'	=> $_min->toTimeString(),
			];

			if(!$is_timeslot && $can_book_from >= $stop) break;
		}
		$data = [];
		$data[$from] = [
			'time_dropdown_options' => $dropdown_options,
			'allow_full_range' => false
		];
		return $data;
	}

	/**
	 *  Get the default time range by date
	 * 
	 *	@param str $date
	 *	@param object $office_hours
	 *  @return array [time_dropdown_options] 
	 */
	private function _getDefaultTimeRange($date, $office_hours, $is_sameDay) {

		// for Room bookings
		if($this->type == self::$TYPE_ROOM) {
			$can_book_from  = Carbon::parse(Carbon::parse($date)->format('Y-m-d').' '.$this->room->booking_from_time);
			$can_book_until = Carbon::parse(Carbon::parse($date)->format('Y-m-d').' '.$this->room->booking_to_time);
		}

		// For hires
		else {

			// grab this day's opening hours 
			$day_hours = $office_hours->{Carbon::parse($date)->format('l')};

			$can_book_from  = Carbon::parse($date.' '.$day_hours->from);
			$can_book_until = Carbon::parse($date.' '.$day_hours->to);

			// ignore office hours?
			if($this->ignore_office_hours == true) {
				$can_book_from  = Carbon::parse($date.' 00:00:00');
				$can_book_until = Carbon::parse($date.' 24:00:00');
			}
		}

		// Fix Today! No bookings in the past... at least not yet:)
		if($can_book_from->isToday()) {
			$can_book_from = Carbon::now()->ceilMinute('15');
		}

		// Check prior to book (in hours)
		$earliest_booking_form_now = Carbon::now()->addHours($this->prior_to_book_hours)->ceilMinute('15');

		// Compare the can book from to the earliest  booking form, and use whichever is greater.
		$can_book_from = $can_book_from > $earliest_booking_form_now ? $can_book_from : $earliest_booking_form_now;
			

		// now we have the from-to range, let's create the dropdown options with the 15min increasement.
		$dropdown_options = null;

		// is this a Room or Hire?
		$min_length_of_booking = $this->room ? $this->room->booking_min_length : $this->hire->booking_min_length;
		$max_length_of_booking = $this->room ? $this->room->booking_max_length : $this->hire->booking_max_length;
		
		$booking_gap = $this->room ? $this->room->booking_gap : $this->hire->booking_gap;


		$stop = $can_book_until->copy()->addMinutes( - (($min_length_of_booking * 60) + $booking_gap) );

		$max = $can_book_until->copy()->addMinutes( - $booking_gap);
		
		// if we have max length defined, calculate the max end-time for this time slot.
		$max_length_allowed = $max_length_of_booking ? $can_book_from->copy()->addHours($max_length_of_booking) : $max;
		

		// Add the first iteration
		$dropdown_options[] = [
			'value' => $can_book_from->toTimeString(),
			'max' 	=> $max > $max_length_allowed ? $max_length_allowed->toTimeString() : $max->toTimeString(),
			'min'	=> $is_sameDay ? $can_book_from->copy()->addHours($min_length_of_booking)->toTimeString() : $can_book_from->toTimeString()
		];

		// loop: create 15min options
		for ($i = 0; $i <= 94; $i ++) {
				
			$can_book_from->addMinutes(15);
			$max_length_allowed = $max_length_of_booking ? $can_book_from->copy()->addHours($max_length_of_booking) : $max;

			$dropdown_options[] = [
				'value' => $can_book_from->toTimeString(),
				'max' 	=> $max > $max_length_allowed ? $max_length_allowed->toTimeString() : $max->toTimeString(),
				'min'	=> $is_sameDay ? $can_book_from->copy()->addHours($min_length_of_booking)->toTimeString() : $can_book_from->toTimeString()
			];

			if($can_book_from >= $stop) break;
		}

		return [
			'time_dropdown_options' => $dropdown_options,
			'allow_full_range' => true
		];

	}


	/**
	 * Organize the Booking collection by Date
	 * 
	 */
	private function _organizeBookingsByDate($bookings) {

		if(!$bookings) return null;

		$booked_dates = new \stdClass();

		foreach($bookings as $booking) {

			$startDateTime = explode(' ', $booking->start);
			$endDateTime = explode(' ', $booking->end);

			$start_date = $startDateTime[0];
			$start_time = $startDateTime[1];
			$end_date = $endDateTime[0];
			$end_time = $endDateTime[1];

			$booked_dates->{$start_date}['user_id'] = $booking->user_id;

			// _single day booking
			if($start_date == $end_date) {

				$booked_dates->{$start_date}['times'][] = [
					'from' => $start_time,
					'to'   => $end_time
				];

			}
			// Multi-day booking
			else {

				// first day
				$booked_dates->{$start_date}['times'][] = [
					'from' => $start_time,
					'to' => '24:00:00'
				];

				// in-between days (Carbon get dates between 2 start_date, end_date)
				$period = CarbonPeriod::create(Carbon::parse($start_date)->addDay(), Carbon::parse($end_date)->addDays(-1));

				foreach ($period as $date) {
					$booked_dates->{$date->format('Y-m-d')}['times'][] = [
						'from' => '00:00:00',
						'to' => '24:00:00'
					];
				}

				// last day
				$booked_dates->{$end_date}['times'][] = [
					'from' => '00:00:00',
					'to' => $end_time
				];
			}
		}
		
		return json_decode(json_encode($booked_dates));
	}


	/**
	 * Get all closed dates from today until the next open day.
	 * 
	 */
	private function _getClosedDates($today, $office_hours) {

		$carbon_days_index = [
			7 => 0,
			1 => 1,
			2 => 2,
			3 => 3,
			4 => 4,
			5 => 5,
			6 => 6,
		];

		$today = $today - 1; // match it with array the index
		$office_hours = array_values( (array) $office_hours);

		$disabled = [];

		foreach($office_hours as $k => $v) {

			if($k <= $today) continue;

			if($v->status == 1) {
				break;
			}

			$disabled[] = $carbon_days_index[$k+1];
		}

		if( ! $disabled ) {
			return [];
		}

		// Get the date by day index
		foreach($disabled as $i => $day) {
			$disabled[$i] = Carbon::now()->next($day)->format('Y-m-d');
		}

		return $disabled;
	}


}
