<?php

namespace App\Http\ViewComposers\Resident;

use Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\Building;


class ViewBuildingComposer
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
        
        $building = Building::getOrSetBuilding();

        if(! $building ) {
            abort(404);
        }

        $building->images = $building->getGalleryImages();

        $building->office_hours = json_decode($building->office_hours);
        $view->with(compact('building'));

    }

}
