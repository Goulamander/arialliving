<?php

namespace App\Http\ViewComposers;

use Auth;
use Route;
use Illuminate\View\View;

use App\Models\Building;
use Session;

class BuildingSwitchComposer
{
    

    /**
     * Instantiate a new instance of the Composer
     *
     * @param Illuminate\Http\Request
     */
    public function __construct() {
    }


    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view) {

        $buildings = Building::myBuildings()
            ->orderBy('name', 'ASC')
            ->get();

        $current_building = null;

        if( Session::get('building_preview_id') ) {

            $current_building = $buildings->filter(function($building) {
                return $building->id == Session::get('building_preview_id');
            })->first();
        }   

        $view->with(compact('buildings', 'current_building'));
    }

}
