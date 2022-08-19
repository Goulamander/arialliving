<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

use Carbon\Carbon;
use Storage;
use Auth;

class LineItem extends Model
{	
    
    use SoftDeletes;

    /**
    * The table associated with the model.
    *
    * @var string
    */
    protected $table = 'line_items';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'item_id',
        'name',
        'desc',
        'price',
        'thumb',
        'status'
    ];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];


    public static $STATUS_ACTIVE = 1;
    public static $STATUS_INACTIVE = 0;
    public static $STATUS_DELETED = 2;




	/***********************************************************************/
	/****************************** FORM CONFIGS  **************************/
	/***********************************************************************/


    /**
	 * Service: Line items
	 * 
	 */
	public static $form_fields = [
        'name' => [
            'validation' => 'required|max:255',
            'class' 	 => [
                'group'  => '',
                'input'  => '',
            ],
            'label' 	 => 'Item Name',
            'type'		 => 'text',
        ],
        'desc' => [
            'validation' => '',
            'class' 	 => [
                'group'  => '',
                'input'  => '',
            ],
            'label' 	 => 'Item Description',
            'type'		 => 'textarea',
        ],
        'row_start',
            'price' => [
                'validation' => 'required',
                'class' 	 => [
                    'group'  => 'col',
                    'input'  => '',
                ],
                'label' 	 => 'Price',
                'type'		 => 'number',
                'description'=> 'Enter 0 for free items',
                'value' => 0,
            ],
            'status' => [
                'validation' => 'required|in:0,1',
                'class' 	 => [
                    'group'  => 'col',
                    'input'  => '',
                ],
                'label'	=> 'Status',
                'type'  => 'select',
                'options' => [
                    0 => 'Inactive',
                    1 => 'Active',
                ],
                'value' => 1
            ],
        'row_end'
    ];
    

    /***********************************************************************/
    /************************* ELOQUENT RELATIONSHIPS ***********************/
    /***********************************************************************/


    
    /**
     * User
     * @return App\Models\User
     */
    public function bookableItem()
    {
        return $this->belongsTo('App\Models\BookableItem', 'id', 'item_id');
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
		return 'items/'.$this->item_id.'/line-items/'.$this->id.'/';
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
