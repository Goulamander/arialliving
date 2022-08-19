<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Role extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';


	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = [
		'name',
		'display_name',
		'description',
    ];



	/***********************************************************************/
	/* ELOQUENT RELATIONSHIPS
    /***********************************************************************/
    
    /**
     * Get the users that belong to this role.
     *
     * @return App\Models\User
     */

    public function users() {
        return $this->hasMany(User::class);
    }


	/***********************************************************************/
	/****************************  LOCAL SCOPES  ***************************/
	/***********************************************************************/

	/**
	 * Scope a query to get admin levels only
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	function scopeAdminLevels($query) {
		return $query->where('id', '<', User::$ROLE_RESIDENT);
    }

	/**
	 * Scope a query to get resident levels only
	 *
	 * @param  \Illuminate\Database\Eloquent\Builder
	 * @return \Illuminate\Database\Eloquent\Builder
	 */
	function scopeResidentLevels($query) {
		return $query->where('id', '>=', User::$ROLE_RESIDENT);
    }



	/***********************************************************************/
	/***************************  HELPER METHODS  **************************/
	/***********************************************************************/


    /**
     * Get the Resident Levels
     */
    public function getAdminLevels() {
        return self::where('id', '<', User::$ROLE_RESIDENT)->get();
    }

    
    /**
     * Get the Resident Levels
     */
    public function getResidentLevels() {
        return self::where('id', '>=', User::$ROLE_RESIDENT)->get();
	}



}
