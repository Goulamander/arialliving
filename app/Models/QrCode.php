<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QrCode extends Model
{

	use SoftDeletes;

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
	protected $table = 'qr_codes';

	protected $fillable = [
		'id',
		'type',
		'name',
		'content',
		'description'
	];

	protected $dates = [
		'created_at',
		'updated_at'
	];


	/***********************************************************************/
	/*************************  STATIC FUNCTIONS  **************************/
	/***********************************************************************/

	public static $STATUS_ACTIVE = 1;
	public static $STATUS_INACTIVE = 0;

	public static function form_fields()
	{
		return [
			//
			'row_start',
			//
			'name' => [
				'validation' => 'required|max:100',
				'class' 	 => [
					'group'  => 'col-12',
					'input'  => '',
				],
				'label' 	 => 'Name',
				'type'		 => 'text',
			],
			'content' => [
				'validation' => 'required|max:200',
				'placeholder' => 'Enter your content of QR Code here...',
				'class' 	 => [
					'group'  => 'col-12',
					'input'  => '',
				],
				'label' 	 => 'Content',
				'type'		 => 'textarea',
				'max' => 200,
			],
			'description' => [
				'validation' => '',
				'placeholder' => 'Enter your notes here...',
				'class' 	 => [
					'group'  => 'col-12',
					'input'  => '',
				],
				'label' 	 => 'Notes',
				'type'		 => 'textarea',
			],
			//
			'row_end',
		];
	}

	/**
	 * Get the status of a user.
	 */
	public function getStatus($is_label = true)
	{

		switch ($this->type) {

			case self::$STATUS_INACTIVE:
				return $is_label ? '<span class="label l-gray">Inactive</span>' : 'Inactive';
				break;

			case self::$STATUS_ACTIVE:
				return $is_label ? '<span class="label l-green">Active</span>' : 'Active';
				break;

			default:
				return '';
		}
	}
}
