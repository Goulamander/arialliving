<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */
    protected $table = 'categories';
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'order',
        'status',
        'color',
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
        //
        'name' => [
            'validation' => 'required|max:255',
            'class' 	 => [
                'group'  => 'col',
                'input'  => '',
            ],
            'label' 	 => 'Category Name',
            'type'		 => 'text',
        ],
        'color' => [
            'validation' => 'required|max:255',
            'class' 	 => [
                'group'  => 'col-3',
                'input'  => '_color',
            ],
            'label' 	 => 'Colour',
            'type'		 => 'color',
        ],
        'row_end',
    ];



    /***********************************************************************/
    /************************* ELOQUENT RELATIONSHIPS ***********************/
    /***********************************************************************/


    /**
     * BookableItems
     * @return App\Models\BookableItem
     */
    public function items()
    {
        return $this->hasMany('App\Models\BookableItem');
    }



	/***********************************************************************/
	/**************************  PUBLIC METHODS  ***************************/
    /***********************************************************************/
    
    /**
	 * Get the Booking type label by the type_id
	 * 
	 * @return String
	 */
	public function statusLabel() {

        switch($this->status) {
            case 1:
                return '<span class="label l-green m-0">Active</span>';
                break;

            case 0:
                return '<span class="label l-red m-0">Inactive</span>';
                break;
        }
    }


    
}
