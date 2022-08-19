<?php

namespace App\Models;

use App\Models\User;
use App\Models\Booking;
use App\Models\BookableItem;
use App\Models\Comment;
use App\Models\Transaction;
use App\Models\Pivot\BuildingUser;
use App\Models\BookableItem\BookableItemEvent;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use Mail;
use App\Mail\NormalEmail;

use BladeExtensions;

use Carbon\Carbon;

use App\Notifications\Resident\BookingCreated;
use App\Notifications\Resident\BookingUpdated;

use App\Notifications\Admin\BookingCreatedAdmin;
use App\Notifications\Admin\BookingUpdatedAdmin;

use Notification;
use DB;
use Auth;

class Booking extends Model
{

	use SoftDeletes;
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'bookings';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'id',
		'user_id',
		'building_id',
		'bookable_item_id',
		'type',
		'start',
		'end',
		'length_str',
		'qty',
		'line_items',
		'subtotal',
		'GST',
		'bond',
		'admin_fee',
		'other_fee',
		'total',
		'accepted_terms',
		'signature',
		'status',
		'booking_comments',
		'cancellation_cutoff_date',
		'cleaning_required'
	];


	protected $hidden = [
		'accepted_terms',
		'signature',
	];


	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = [
		'start',
		'end'
	];

	protected $casts = [
		'other_fee' => 'array',
];



	public static $STATUS_ACTIVE    = 1; // Booking without payments
	public static $STATUS_CONFIRMED = 2; // Bookings with payment
	public static $STATUS_CANCELED  = 3;
    public static $STATUS_COMPLETE  = 4;
    public static $STATUS_PAYMENT_FAILED = 5;
    public static $STATUS_ARCHIVE   = 6;


	
	// Booking type
    public static $TYPE_ROOM = 1;
    public static $TYPE_HIRE = 2;
	public static $TYPE_EVENT = 3;
	public static $TYPE_SERVICE = 4;
	
	// Admin cleaning required status
	public static $ADMIN_CLEANING_REQUIRED = 1;
	public static $ADMIN_CLEANING_NO_REQUIRED = 0;



	/***********************************************************************/
	/****************************** FORM CONFIGS  **************************/
	/***********************************************************************/

	/**
	 * Create / Edit a Booking Form.
	 * 
	 */
	public static $form_fields = [
		'booking_type' => [
			'validation' => 'required',
			'class' 	 => [
				'group'  => '',
				'input'  => '',
			],
			'label' 	 => 'Select Booking type',
			'type'		 => 'select',
			'options'	 => [
				1 => 'Room Booking',
				2 => 'Hire',
				3 => 'Event Booking',
				4 => 'Service',
			],
		],
		'bookable_item' => [
			'validation' => 'required',
			'class' 	 => [
				'group'  => '',
				'input'  => '',
			],
			'label' 	 => 'Select Room/Service/Hire/Event',
			'type'		 => 'text',
			'data'		 => [
				'autocomplete' => true,
				'source' => 'item',
				'return' => 'id',
				'query'  => '', // pre-filter results -> booking_type & building
			]
		],
		// dynamic fields will be injected hire once bookable_item has selected
		'user' => [
			'validation' => 'required',
			'class' 	 => [
				'group'  => '',
				'input'  => '',
			],
			'label' 	 => 'Select Resident',
			'type'		 => 'text',
			'data'		 => [
				'autocomplete' => true,
				'source' => 'resident',
				'return' => 'user_id',
			]
		],
		'building' => [
			'validation' => 'required',
			'class' 	 => [
				'group'  => '',
				'input'  => '',
			],
			'label' 	 => 'Building',
			'type'		 => 'text',
			'data'		 => [
				'autocomplete' => true,
				'source' => 'building',
				'return' => 'id',
			]
		],
		// insert row start
		'row_start',
		//
		'start'  => [
			'validation' => 'required',
			'class' 	 => [
				'group' => 'col',
				'input' => '',
			],
			'label' 	 => 'Booking Start',
			'type'		 => 'date'
		],
		'end'  => [
			'validation' => '',
			'class' 	 => [
				'group' => 'col',
				'input' => '',
			],
			'label' 	 => 'Booking End',
			'type'		 => 'date'
		],
		// insert row start
		'row_end',
		//
	];


	
	/***********************************************************************/
	/************************* ELOQUENT RELATIONSHIPS **********************/
	/***********************************************************************/

    /**
     * Booking type: Event
     * @return App\Models\Booking\BookingEvent
     */
    public function event()
    {
        return $this->hasOne('App\Models\Booking\BookingEvent');
    }


	/**
	 * User
	 * @return App\Models\User
	 */
	public function user() {
		return $this->belongsTo('App\Models\User');
	}


	/**
	 * Building
	 * @return App\Models\Building
	 */
	public function building() {
		return $this->belongsTo('App\Models\Building');
	}

	/**
	 * Bookable item
	 * @return App\Models\BookableItem
	 */
	public function bookableItem() {
		return $this->belongsTo('App\Models\BookableItem', 'bookable_item_id', 'id')->withTrashed();
	}


	/**
	 * Transactions
	 * @return App\Models\Transaction
	 */
	public function transactions() {
		return $this->hasMany('App\Models\Transaction', 'booking_id', 'id')->orderBy('id', 'DESC');
	}

	/**
	 * Comments
	 * @return App\Models\Comment
	 */
	public function comments() {
		return $this->hasMany('App\Models\Comment', 'booking_id', 'id');
	}



	/***********************************************************************/
	/****************************  LOCAL SCOPES  ***************************/
	/***********************************************************************/


	/**
	 * Scope a query to get bookings only accessible to the logged-in user.
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	function scopeMyBooking($query) {

		$user = Auth::user();

		//  Admins: Can see all bookings
		if( $user->isSuperAdmin() || $user->isAdmin() ) {
			return $query;
		}

		//  3rd party see all assign
		if( $user->isExternal() ) {
			return $query;
		}
		
		// Admin: todo 

		// Own only: Building Manager, Staff
		return $query->whereRaw("bookings.building_id IN (SELECT GROUP_CONCAT(DISTINCT(building_id)) as building_ids FROM building_user WHERE user_id = {$user->id} AND relation_status = 1)");
		
	}
	
	/**
	 * Scope complete bookings with bond
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	function scopeHaveBondToRelease($query) {

		// when booking is complete and has bond: Admin needs to release the bond
		return $query->where('bookings.status', self::$STATUS_COMPLETE)
					 ->where('bond', '>', 0);
	}



	/***********************************************************************/
	/**************************  PUBLIC METHODS  ***************************/
	/***********************************************************************/


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
	public function isComplete() {
		return $this->status == self::$STATUS_COMPLETE ? true : false;
	}
	/**
	 * us this booking editable?
	 */
	public function isEditable() {
		return in_array($this->status, [self::$STATUS_ACTIVE, self::$STATUS_PAYMENT_FAILED]) ? true : false;
	}

	/**
	 * is this booking cancelled?
	 */
	public function isCancelled() {
		return in_array($this->status, [self::$STATUS_CANCELED]) ? true : false;
	}

	/**
	 * admin cleaning required
	 */
	public function isAdminCleaningRequired() {
		return isset($this->cleaning_required) && $this->cleaning_required == self::$ADMIN_CLEANING_REQUIRED;
	}

	/**
	 * admin no cleaning required
	 */
	public function isAdminNoCleaningRequired() {
		return isset($this->cleaning_required) && $this->cleaning_required == self::$ADMIN_CLEANING_NO_REQUIRED;
	}

	/**
	 * bookable_item_fee
	 */
	public function hasBookableItemFee() {
		if ($this->bookableItem->type == BookableItem::$TYPE_ROOM) {
			return $this->bookableItem->bookableItemFees()->exists();
		}
		return false;
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
		return $comment->get_comments(['booking_id' => $this->id], $offset, $limit);		
	}


	/**
	 * Get the Booking type label by the type_id
	 * 
	 * @return String
	 */
	public function typeLabel($url = false) {
		
		switch($this->type) 
		{
			case self::$TYPE_ROOM:
				return $url ? "room" : "Room/Area Booking";
				break;

			case self::$TYPE_HIRE:
				return $url ? "hire" : "Hire";
				break;
				
			case self::$TYPE_EVENT:
				return $url ? "event" : "Event";
				break;

			case self::$TYPE_SERVICE:
				return $url ? "service" : "Service";
				break;

			default:
				return "";
		}
	}


	/**
	 * Get the Booking type label by the type_id
	 * 
	 * @return String
	 */
	public function statusLabel() {

		switch($this->status) {

			case self::$STATUS_ACTIVE:
				return '<span class="label l-green m-0">Active</span>';
				break;

			case self::$STATUS_CONFIRMED:
				return '<span class="label l-green m-0">Confirmed</span>';
				break;

			case self::$STATUS_CANCELED:
				return '<span class="label l-red m-0">Canceled</span>';
				break;

			case self::$STATUS_COMPLETE:
				return '<span class="label l-gray m-0">Complete</span>';
				break;

			case self::$STATUS_PAYMENT_FAILED:
				return '<span class="label l-red m-0">Payment Failed</span>';
				break;

			case self::$STATUS_ARCHIVE:
				return '<span class="label l-gray m-0">Archive</span>';
				break;
		}
		return '';
	}

	/**
	 * show event date
	 * @return str type string
	 */
	public function showEventDate() {
		return ($this->bookableItem->event->event_type == BookableItemEvent::$TYPE_SINGLE) ? bookingDate($this->start, $this->end) : dateFormat($this->bookableItem->recurring->repeat_next);
	}

	/**
	 * show event time
	 * @return str type string
	 */
	public function showEventTime() {
		return ($this->bookableItem->event->event_type == BookableItemEvent::$TYPE_SINGLE) ? timeFormat($this->start, $this->end) : NULL;
	}


	/**
	 * Get the jobs's Number
	 *
	 * @return string
	 */
	public function getNumber() {
		return sprintf('B%04d', $this->id);
	}
	

	/**
	 * Get the jobs's Number
	 *
	 * @return string
	 */
	public function getEventTitle() {

		switch($this->type) {
			
			case self::$TYPE_EVENT:
				return $this->event->attendees_num ? $this->user->fullName().' +'.$this->event->attendees_num : $this->user->fullName();
				break;

			case self::$TYPE_HIRE:
				return $this->user->fullName();
				break;

			default:
				return $this->user->fullName();
				break;
		}
	}


	/**
	 * Get the Booking details for the email body.
	 * 
	 * @return html
	 */
	public function getBookingDetails($is_html = true) {
		$body = "";

		$cleaning_required = '';
		if(($this->user->isSuperAdmin() || $this->user->isAdmin()) && ($this->isAdminCleaningRequired() || $this->isAdminNoCleaningRequired())){
			$cleaning_required = $this->isAdminCleaningRequired() ? 'Yes' : 'No';
		}
		if ($this->other_fee && count($this->other_fee) > 0) {
			foreach ($this->other_fee as $fee) {
				$cleaning_required .= $fee['name'] .': ' . priceFormat($fee['fee']);
			}
		}

		$resident_details = "
			<p>Resident details:</p>
			<ul>
				<li>Resident Name: <span>{$this->user->fullName()}</span></li>
				<li>Resident Email: <span>{$this->user->email}</span></li>
				<li>Resident Phone: <span>{$this->user->phone}</span></li>
			</ul>
		";

		$resident_comments = "
			<p>Resident Comments:</p>
			<ul>
				<li>Comments: <span>{$this->booking_comments}</span></li>
			</ul>
		";

		switch($this->type) {

			// Room type bookings
			case self::$TYPE_ROOM:

				if($is_html) {
					$body = "
					<p>Booking details:</p>
					<ul class='booking-details'>
						<li>Booking No: <span>{$this->getNumber()}</span></li>
						<li>Time of booking submission: <span>".bookingDate($this->created_at, $this->created_at)."</span></li>
						<li><span>{$this->bookableItem->title}</span></li>
						<li>Date: <span>".bookingDate($this->start, $this->end)."</span></li>
						<li>From/To: <span>".bookingTime($this->start, $this->end)."</span></li>
						<li>Length: <span>{$this->length_str}</span></li>
						<li>Cleaning Required: <span>".$cleaning_required."</span></li>
					</ul>
					$resident_details
					$resident_comments
					";
				}
				else {
					// SMS body
					$body = $this->bookableItem->title."\n";
					$body .= "Date: ".bookingDate($this->start, $this->end)."\n";
					$body .= "From/To: ".bookingTime($this->start, $this->end)."\n";
					$body = "Length: ".$this->length_str;
				}
				break;
			
			// Hire type bookings
			case self::$TYPE_HIRE:

				if($is_html) {

					$body = "
						<p>Booking details:</p>
						<ul class='booking-details'>
							<li>Booking No: <span>{$this->getNumber()}</span></li>
							<li>{$this->qty} x <span>{$this->bookableItem->title}</span></li>
							<li>Date: <span>".bookingDate($this->start, $this->end)."</span></li>
							<li>Pickup/Drop off: <span>".bookingTime($this->start, $this->end)."</span></li>
							<li>Length: <span>{$this->length_str}</span></li>
							<li>Booking Total: <span>".priceFormat($this->total)."</span></li>
							";
					// with deposit
					if($this->bond && $this->bond > 0) {
						$body .= "
							<li>Security Deposit <span>".priceFormat($this->bond)."</span></li>";
							// <li>
							// 	<span>Your payment of the rental fee and bond of ".priceFormat($this->total + $this->bond)." will be charged on ".dateFormat($this->bookableItem->getCutOffDate($this->start)).".</span></br>
							// 	<span>".__('messages.booking.payment_note_sub')."</span>
							// </li>";
					}
					// with no deposit
					else {
						$body .= "
							<li>Your payment of the rental fee of ".priceFormat($this->total)." will be charged on ".dateFormat($this->bookableItem->getCutOffDate($this->start))."</li>";
					}
					$body .= "
						</ul>
						$resident_details
						$resident_comments";

				}
				else {
					// SMS body
					$body = "{$this->qty} x {$this->bookableItem->title}\n";
					$body .= "Date: ".bookingDate($this->start, $this->end)."\n";
					$body .= "Pickup/Drop off: ".bookingTime($this->start, $this->end)."\n";
					$body .= "Length: ".$this->length_str."\n";
					$body .= "Booking Total: ".priceFormat($this->total);
				}
				break;

			// Type Event
			case self::$TYPE_EVENT:
				if($is_html) {

					// $body = "
					// 	<p>Booking details:</p>
					// 	<ul class='booking-details'>
					// 		<li>Booking No: <span>{$this->getNumber()}</span></li>
					// 		<li><span>{$this->bookableItem->title}</span></li>
					// 		<li>Date: <span>".bookingDate($this->start, $this->end)."</span></li>
					// 		<li>From/To: <span>".bookingTime($this->start, $this->end)."</span></li>	
					// 		<li>Num of attendees: <span>{$this->event->attendees_num}</span></li>	
					// 	</ul>
					// 	$resident_details
					// 	$resident_comments";

					// $body = "
					// 	<p>Booking details:</p>
					// 	<ul class='booking-details'>
					// 		<li>Booking No: <span>{$this->getNumber()}</span></li>
					// 		<li><span>{$this->bookableItem->title}</span></li>
					// 		<li>Date: <span>".$this->showEventDate()."</span></li>
					// 		<li>From/To: <span>".$this->showEventTime()."</span></li>	
					// 		<li>Num of attendees: <span>{$this->event->attendees_num}</span></li>	
					// 	</ul>
					// 	$resident_details
					// 	$resident_comments";
					$body = "
						<p>Booking details:</p>
						<ul class='booking-details'>
							<li>Booking No: <span>{$this->getNumber()}</span></li>
							<li><span>{$this->bookableItem->title}</span></li>
							<li>Num of attendees: <span>{$this->event->attendees_num}</span></li>	
						</ul>
						$resident_details
						$resident_comments";
				}
				else {
					// SMS body
					$body = $this->bookableItem->title."\n";
					// $body .= "Date: ".bookingDate($this->start, $this->end)."\n";
					// $body .= "From/To: ".bookingTime($this->start, $this->end)."\n";
					$body .= "Date: ".$this->showEventDate()."\n";
					$body .= "From/To: ".$this->showEventTime()."\n";
					$body .= "Length: ".$this->length_str."\n";
					$body .= "Num of attendees: ".$this->event->attendees_num."\n";
				}
				break;

			// Service Type
			case self::$TYPE_SERVICE:
				if($is_html) {

					$body = "
						<p>Booking details:</p>
						<ul class='booking-details'>
							<li>Booking No: <span>{$this->getNumber()}</span></li>
							<li><span>{$this->bookableItem->title}</span></li>";
							// if($this->bookableItem->service->is_date) {
							// 	$body .= "<li>{$this->bookableItem->service->date_field_name} <span>".$this->start."</span></li>";
							// }
					$body .= "
						</ul>";
					$body .= "
						<ul class='line-items'>";
						$line_items = json_decode($this->line_items);
						foreach($line_items as $line_item) {
							// attach the line item's name to the cart item
							$line_item->name = $this->bookableItem->line_items->first(function($it) use($line_item) {
								return $it->id == $line_item->id;
							})->name;

							$body .= "
								<li>{$line_item->qty} x {$line_item->name}</li>";
						}
						
					$body .= "
						<li>Subtotal <span>".priceFormat($this->subtotal)."</span></li>";
					$body .= "<li>Booking Date: <span>".bookingDate($this->start, $this->end)."</span></li>";
					if($this->bookableItem->admin_fee) {
						$body .= "
							<li>Admin fee <span>".priceFormat($this->bookableItem->admin_fee)."</span></li>
						";
					}

					$body .= "
						<li>Total <span>".priceFormat($this->total)."</span></li>
					</ul>
					$resident_details
					$resident_comments
					";
				}
				else {
					// SMS body
					$body = "{$this->bookableItem->title}\n";

					if($this->bookableItem->service->is_date) {
						$body .= "{$this->bookableItem->service->date_field_name}: ".$this->start."\n";
					}
					$body .= "Items:\n";
					$line_items = json_decode($this->line_items);
					foreach($line_items as $line_item) {
						// attach the line item's name to the cart item
						$line_item->name = $this->bookableItem->line_items->first(function($it) use($line_item) {
							return $it->id == $line_item->id;
						})->name;

						$body .= "{$line_item->qty} x {$line_item->name}\n";
					}
					$body .= "Total: ".priceFormat($this->total);
				}
				break;
		}

		return $body;
	}


	/**
	 * Get the booking instructions is HTML format for the email body.
	 * 
	 * @return HTML
	 */
	public function getBookingInstructions() {
		if( ! $this->bookableItem->booking_instructions ) {
			return "";
		}
		return "<small>{$this->bookableItem->booking_instructions}</small>";
	}


	/**
	 * Get the refund link for a booking
	 * 
	 * @return HTML
	 */
	public function getRefundUrl() {

		// check if booking is in a valid status for refund. 

		// Nothing to refund
		if($this->total == 0) {
			return false;
		}

		// No payment made yet
		if( in_array($this->status, [self::$STATUS_ACTIVE, self::$STATUS_PAYMENT_FAILED]) ) {
			return false;
		}

		// There is no transaction(s) found
		if($this->transactions->isEmpty()) {
			return false;
		}
			
		$payment_to_refund = $this->transactions
				->filter
				->isBookingFee()
				->isSuccessful()
				->values();
		
		if(!$payment_to_refund) {
			return false;
		}

		return "TODO";
	}


	/**
	 * Get the booking instructions is HTML format for the email body.
	 * 
	 * @return HTML
	 */
	public function attachCalendarConfig() {

		// No calendar config for Events
		if( $this->type == self::$TYPE_EVENT ) {
            return;
		}

		// No calendar config for Services that has no date
		if( $this->type == self::$TYPE_SERVICE && !$this->start) {
			return;
		}

		// Range for the initial Calendar Setup when editing the room|hire bookings.
		if(in_array($this->type, [self::$TYPE_ROOM, self::$TYPE_HIRE]) ) {
			$this->_calendar_start = $this->start ? Carbon::parse($this->start)->addDays(-20)->format('Y-m-d') : '';
			$this->_calendar_end = $this->end ? Carbon::parse($this->end)->addDays(+20)->format('Y-m-d') : '';
		}


		// Set the Calendar mode (single|range)

		$allow_multiday = false;

		if($this->type == self::$TYPE_ROOM) {
			$allow_multiday = $this->bookableItem->room->allow_multiday;
		}
		if($this->type == self::$TYPE_HIRE) {
			$allow_multiday = $this->bookableItem->hire->allow_multiday;
		}
		$this->_calendar_mode = $allow_multiday ? 'range' : 'single';
	

		// FlatPicker receives: strong or array depending on the mode
		$start_date = Carbon::parse($this->start)->format('Y-m-d');
		$end_date = Carbon::parse($this->end)->format('Y-m-d');

		if($start_date == $end_date) {
			$this->_calendar_default_date = $start_date;
		}
		else {
			$this->_calendar_default_date = [$start_date, $end_date];
		}
		return;
	}
	

	/**
	 * Cancel a booking
	 * @pa
	 */
	public function cancel() {
		
		// Set status to canceled
		$this->status = self::$STATUS_CANCELED;
		$this->save();

		// Soft delete
		$this->delete();
		
		// Send Cancel Notification
		$this->sendBookingCancellation();
	}
  



    /***********************************************************************/
    /*******************************  Emails  ******************************/
    /***********************************************************************/


	/**
     * Booking Confirmation Email
     * 
	 * @param bool $is_update
     * @return void
     */
    public function sendBookingConfirmation($is_update = false)
    {
		$when = Carbon::now()->addMinutes(3);
		$user = Auth::user();
		// -- Notify Residents
		$user->notify( ($is_update ? new BookingUpdated($this) : new BookingCreated($this)) );

		// -- Notify Admins

		// Get all admins who assigned to this building.
		$building_user = BuildingUser::where([
				'building_id' => $this->building_id,
				'relation_status' => BuildingUser::$STATUS_ACTIVE,
				'relation_type'=> BuildingUser::$RELATION_TYPE_MANAGEMENT
			])
			->pluck('user_id')
			->toArray();

		// Garb all notifiable users
		$Admins = User::where(function ($query) {
					$query->where('id', '!=', Auth::id());
					$query->where('status','1');
					// $query->whereIn('role_id', [User::$ROLE_SUPER_ADMIN, User::$ROLE_ADMIN]);
					// $query->whereIn('id', $Admin_ids);
					if($this->isService()) {
						$query->where('id', $this->bookableItem->service->assign_to_user_id);
					}
					$query->orWhereIn('role_id', [User::$ROLE_SUPER_ADMIN]);
				})
			//   ->orWhereIn('role_id', [User::$ROLE_BUILDING_MANAGER, User::$ROLE_STAFF, User::$ROLE_EXTERNAL])
			//   ->whereIn('id', $Admin_ids)
			//   ->where('id', '!=', Auth::id())
			->orWhereIn('id',$building_user)
			->with('settings')
			->get();
		// Notification::send($Admins, ($is_update ? new BookingUpdatedAdmin($this) : new BookingCreatedAdmin($this))->delay($when));
		Notification::send($Admins, ($is_update ? new BookingUpdatedAdmin($this) : new BookingCreatedAdmin($this)));
		return;
	}

	/**
     * Booking Reminder Email
     * 
     * @return void
     */
    public function sendBookingReminder()
    {
		libxml_use_internal_errors(true);

		// Build email subject
		$setting_subject = Setting::where('code', 'email.templates.booking.reminder.subject')->first(['replace', 'value']);
		$subject = modifyHtmlToBladeCode($setting_subject->replace, $setting_subject->value);
		$subject = BladeExtensions::compileString($subject, [
			'booking' => $this,
		]);

		// Build email body
		$setting_content = Setting::where('code', 'email.templates.booking.reminder.content')->first(['replace', 'value']);
		$content = modifyHtmlToBladeCode($setting_content->replace, $setting_content->value);
		$content = BladeExtensions::compileString($content, [
			'booking' => $this,
			'link'    => ''
		]);

		// No empty emails
		if(!$subject || !$content) {
			return;
		}
		
		// Send
		Mail::to($this->user->email)
			->queue( new NormalEmail($subject, $content));

        return;
	}
	


	/**
     * Booking Cancellation Email
     * 
     * @return void
     */
    public function sendBookingCancellation()
    {
		libxml_use_internal_errors(true);

		
		// Build email subject
		$setting_subject = Setting::where('code', 'email.templates.booking.cancellation.subject')->first(['replace', 'value']);

		$subject = modifyHtmlToBladeCode($setting_subject->replace, $setting_subject->value);
		$subject = BladeExtensions::compileString($subject, [
			'booking' => $this,
		]);

		// Build email body
		$setting_content = Setting::where('code', 'email.templates.booking.cancellation.content')->first(['replace', 'value']);
		$content = modifyHtmlToBladeCode($setting_content->replace, $setting_content->value);
		$content = BladeExtensions::compileString($content, [
			'booking' => $this,
			'link'    => ''
		]);

		

		// No empty emails
		if(!$subject || !$content) {
			return;
		}
		
		// Send
		Mail::to($this->user->email)
			->queue( new NormalEmail($subject, $content));

        return;
	}
	

	// todo:

	// Payment receipt... 
	// Payment refund
	// Bond release note 


	// Event: RSVP ?
	// Event: Changes Note
	// Event: Cancellation Note


}
