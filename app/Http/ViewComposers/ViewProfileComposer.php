<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\BookableItem;
use App\Models\UserSetting;

use File;
use Storage;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use Illuminate\Support\Str;


class ViewProfileComposer
{
    
    /**
     * Instantiate a new instance of the Composer
     *
     * @param Illuminate\Http\Request
     */
    public function __construct(Request $request) {

    }



    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view) {

        $user = Auth::user();

        // Grab the Notification options
        $notification_options = isset(UserSetting::$notification_types[$user->role->name]) ? UserSetting::$notification_types[$user->role->name] : '';

        // Send the data to the view
        $view->with(compact('user', 'notification_options'));
    }





}
