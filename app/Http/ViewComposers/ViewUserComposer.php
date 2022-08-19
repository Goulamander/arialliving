<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\User;
use App\Models\UserSetting;

class ViewUserComposer
{
	
	/**
     * The user's id
     * @var int
     */

    protected $user_id;


    /**
     * Instantiate a new instance of the Composer
     *
     * @param Illuminate\Http\Request  $request
     * @param App\Services\PaginationService
     */

     public function __construct(Request $request) {
        $this->user_id = $request->user_id ?: Auth::id();
     }


    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view) {

        $user = User::where('id', $this->user_id)
                    ->with('buildings')
                    ->with('bookings')
                    ->withTrashed()
                    ->firstOrFail();

        // Grab the Notification options
        $notification_options = isset(UserSetting::$notification_types[$user->role->name]) ? UserSetting::$notification_types[$user->role->name] : '';

        // Only SuperAdmins can edit the users.
        $can_edit = Auth::user()->isSuperAdmin() ? true : false;

        // Send the data to the view
		$view->with(compact('user', 'notification_options', 'can_edit'));

	}
	
}
