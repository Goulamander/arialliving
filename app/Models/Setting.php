<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
	
	/**
	 * The table associated with the model.
	*
	* @var string
	*/
	protected $table = 'settings';



	/***********************************************************************/
	/*************************  STATIC FUNCTIONS  **************************/
	/***********************************************************************/


	/**
	 *
	 *  Get Settings by group
	 * 	
	 * @param str $group - Setting Group tom return
	 * @return Array
	 */

	public static function getSettings($group) {

		$settings = self::where('group', $group)->get();

		if(!$settings) {
			return null;
		}

		$obj = [];
		foreach ($settings as $setting) {
			$obj[str_replace($group . '.','', $setting->code)] = $setting->value;
		}
		
		return $obj;
	}
	
	
	/**
	 * 
	 */
	public static function getSettingByCode($code) {
      
      $setting = self::where('code', $code)->first(['value']);
		if(!$setting) return array('error', 'code '.$code.' not found.');

		return $setting->value;
	}


}
