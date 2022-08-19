<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

use DB;
use App\Models\Building;
use App\Models\Pivot\BuildingUser;


class ViewBuildingComposer
{
    
    /**
     * The ID of the Location
     * @var int
     */
    protected $building_id;



    /**
     * Instantiate a new instance of the Composer
     *
     * @param Illuminate\Http\Request
     */
    public function __construct(Request $request)
    {
        $this->building_id = $request->building_id;
    }



    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view) {

        $building = Building::where('id', $this->building_id)
            ->with('page_content')
            ->firstOrFail();
        
        // Add files
        $building->images = $building->getGalleryImages();
        $building->thumbs = $building->getGalleryThumbs();

        // Grab the 30 most recent comments
        $building->comments =  $building->get_comments(0, 30);


        // convert to array the office hours
        $building->office_hours = json_decode($building->office_hours, true);
      
        $building_staff = BuildingUser::select(DB::raw("
            relation_type,
            users.id,
            concat(users.first_name, ' ', users.last_name) as name,
            users.email,
            users.mobile,
            roles.display_name as role
        "))
        ->where('building_id', $building->id)
        ->where('relation_status', true)
        ->whereIn('relation_type', [BuildingUser::$RELATION_TYPE_MANAGEMENT, BuildingUser::$RELATION_TYPE_STAFF])
        ->leftJoin('users', 'users.id', '=', 'building_user.user_id')
        ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
        ->orderBy('role_id', 'ASC')
        ->get();

        $view->with(compact(
            'building',
            'building_staff'
        ));
    }

}
