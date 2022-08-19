<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{

	public $timestamps = false;


	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'user_settings';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'notifications_email',
		'notifications_sms',
		'additional_password_prompt'
	];

	public static $ADDITIONAL_PASSWORD_PROMPT_ON = 1; 
	public static $ADDITIONAL_PASSWORD_PROMPT_OFF = 0; 
	public static $ADDITIONAL_PASSWORD_PROMPT_LIMIT = 100; 


	/**
	 * 
	 * Notification types per user level.
	 * 
	 */
	public static $notification_types = [

		'super-admin' => [
			'new_booking' 	 => 'a new booking is placed',
			'new_order' 	 => 'a new service order is placed',
			'update_booking' => 'a booking is updated',
			'cancel_booking' => 'a booking is cancelled',
		],
		'building-manager' => [
			'new_booking' 	 => 'a new booking is placed',
			'new_order' 	 => 'a new service order is placed',
			'update_booking' => 'a booking is updated',
			'cancel_booking' => 'a booking is cancelled',
		],
		'admin' => [
			'new_booking' 	 => 'a new booking is placed',
			'new_order' 	 => 'a new service order is placed',
			'update_booking' => 'a booking is updated',
			'cancel_booking' => 'a booking is cancelled',
		],
		'external' => [
			'new_order' => 'When a new order is placed',
		],

		// 'resident' => [
		// 	'new_booking' 	 => 'New Bookings',
		// 	'update_booking' => 'Booking update',
		// 	'cancel_booking' => 'New Bookings',
		// ],
	];


}
