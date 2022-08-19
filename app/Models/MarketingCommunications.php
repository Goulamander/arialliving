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
use BladeExtensions;

use Mail;
use App\Mail\NormalEmail;

use CardDetect\Detector;
use Eway\Rapid as Eway;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Messages\NexmoMessage;

use Illuminate\Notifications\Notifiable;

use Illuminate\Support\Facades\Hash;

class MarketingCommunications extends Model
{
	use SoftDeletes;
	use Notifiable;

	protected $table = 'marketing_communications';

	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'subject',
		'body',
		'replace',
		'send_via',
		'receiver',
		'status'
	];

	/**
	 * The attributes that should be mutated to dates.
	 *
	 * @var array
	 */
	protected $dates = ['deleted_at', 'created_at', 'updated_at'];


	// Send via type
	public static $SEND_VIA_EMAIL = 0;
	public static $SEND_VIA_SMS = 1;

	// Status 
	public static $STATUS_DRAFT = 0;
	public static $STATUS_SEND = 1;

	/***********************************************************************/
	/****************************** FORM CONFIGS  **************************/
	/***********************************************************************/

	/**
	 * Create / Edit with Resident level
	 * 
	 * @return array -- form fields
	 */
	public static function form_fields()
	{

		// add the resident levels
		$resident_levels = Role::select('id', 'display_name')
			->where('id', '>=', User::$ROLE_RESIDENT)
			->get()
			->keyBy('id')
			->toArray();
		$residents = User::select('id', 'first_name', 'last_name')
			->where('role_id', $resident_levels[User::$ROLE_RESIDENT]['id'])
			->get()
			->keyBy('id')
			->toArray();

		$resident_levels = array_map(function ($r) {
			return $r['display_name'];
		}, $resident_levels);

		$residents = array_map(function ($r) {
			return $r['first_name'] . ' ' . $r['last_name'];
		}, $residents);

		return [
			//
			'row_start',
			//
			'resident_levels' => [
				'validation' => 'required',
				'class' 	 => [
					'group' => 'col-12',
					'input' => '',
				],
				'label' 	 => 'Resident Level',
				'description' => '',
				'type'		 => 'select',
				'options'	 => $resident_levels,
				'value' => null,
			],
			'receiver' => [
				'validation' => 'required',
				'class' 	 => [
					'group'  => 'col-12',
					'input'  => '',
				],
				'label' 	 => 'Resident',
				'description' => '',
				'type'		 => 'multi-select',
				'options'	 => $residents,
			],
			'send_via' => [
				'object_key' => 'send_via',
				'validation' => 'required',
				'class' 	 => [
					'group'  => 'col-12',
					'input'  => '',
				],
				'label' 	 => 'Send Via',
				'type'		 => 'radio',
				'options' => [
					self::$SEND_VIA_EMAIL	=> 'Email',
					self::$SEND_VIA_SMS	=> 'SMS',
				],
				'value' => self::$SEND_VIA_EMAIL,
			],
			'subject' => [
				'validation' => 'required|max:255',
				'class' 	 => [
					'group'  => 'col-12',
					'input'  => '',
				],
				'label' 	 => 'Subject',
				'type'		 => 'text',
			],
			//
			'row_end',
		];
	}
	/**
	 * Create / Edit with building
	 * 
	 * @return array -- form fields
	 */
	public static function building_form_fields()
	{

		$buildings = Building::get([
			'id',
			'name',
			'suburb',
			'postcode',
			'is_thumb'
		])
			->keyBy('id')
			->toArray();

		$buildings = array_map(function ($r) {
			return $r['name'];
		}, $buildings);


		return [
			//
			'row_start',
			//
			'building_id' => [
				'object_key'  => 'building.id',
				'validation'  => 'required',
				'class' 	 => [
					'group'  => 'col-12',
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
			'receiver' => [
				'validation' => 'required',
				'class' 	 => [
					'group'  => 'col-12',
					'input'  => '',
				],
				'label' 	 => 'Resident',
				'description' => '',
				'type'		 => 'multi-select',
				'options'	 => [],
			],
			'send_via' => [
				'object_key' => 'send_via',
				'validation' => 'required',
				'class' 	 => [
					'group'  => 'col-12',
					'input'  => '',
				],
				'label' 	 => 'Send Via',
				'type'		 => 'radio',
				'options' => [
					self::$SEND_VIA_EMAIL	=> 'Email',
					self::$SEND_VIA_SMS	=> 'SMS',
				],
				'value' => self::$SEND_VIA_EMAIL,
			],
			'subject' => [
				'validation' => 'required|max:255',
				'class' 	 => [
					'group'  => 'col-12',
					'input'  => '',
				],
				'label' 	 => 'Subject',
				'type'		 => 'text',
			],
			//
			'row_end',
		];
	}

	/**
	 * Get the status.
	 */
	public function getStatus($is_label = true)
	{

		switch ($this->status) {

			case self::$STATUS_DRAFT:
				return $is_label ? '<span class="label l-gray">Draft</span>' : 'Draft';
				break;

			case self::$STATUS_SEND:
				return $is_label ? '<span class="label l-green">Send</span>' : 'Send';
				break;

			default:
				return '';
		}
	}

	/**
	 * Get the send via.
	 */
	public function getSendVia()
	{

		switch ($this->send_via) {

			case self::$SEND_VIA_EMAIL:
				return 'Email';
				break;

			case self::$SEND_VIA_SMS:
				return 'SMS';
				break;

			default:
				return '';
		}
	}

	/**
	 * Send email
	 */
	public function sendEmail()
	{
		$receivers = explode(",", $this->receiver);
		foreach ($receivers as $receiver) {
			$user = $this->getUserInfo($receiver);
			if ($user) {
				// Send Email
				Mail::to($user->email)->queue(new NormalEmail($this->subject, $this->body));
			}
		}
		return;
	}

	/**
	 * Send sms
	 */
	public function sendSms()
	{
		$receivers = explode(",", $this->receiver);
		foreach ($receivers as $receiver) {
			$user = $this->getUserInfo($receiver);
			if ($user) {
				// Send SMS
				(new NexmoMessage)->content($this->body)->unicode();
			}
		}
	}

	/**
	 * Send sms
	 */
	public function getUserInfo($id)
	{
		return User::find($id);
	}
}
