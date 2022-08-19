<?php

namespace App\Http\ViewComposers\Resident;

use Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\BookableItem;

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

        // Send the data to the view
        $view->with(compact('user'));
    }





}
