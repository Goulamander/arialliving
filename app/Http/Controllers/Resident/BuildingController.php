<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests\StoreBuildingRequest;

// use App\Events\LocationUpdated;

use App\Models\User;
use App\Models\Building;
use App\Models\BookableItem;
use App\Models\Comment;

use App\Models\FilePond;

use DataTables;
use DB;
use Auth;
use File;

use Carbon\Carbon;


class BuildingController extends Controller
{

    /**
     * Instantiate a new controller instance
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('role:super-admin|building-manager|admin|resident|resident-vip');
    }



    /**
     * Display the Building List
     *
     * @return Response
     */
    public function index() {  
        return view(Route::currentRouteName());
    }



    /**
     * Building single page view
     *
     * @return Response
     */
    public function show() {
        return view(Route::currentRouteName());
    }


    /**
     * Get a Building
     *
     * @param  int $building_id
     * @param  bool $with_relations
     * @return Response
     */

    public function get($building_id, $with_relations = true)
    {
        if(!$building_id) {
            abort('404');
        }

        if(!$with_relations) {
            $building = Building::where('id', $building_id)->firstOrFail();
        }
        else {
            $building = Building::where('id', $building_id)
                ->with('users')
                ->with('bookings')
                ->with('bookableItems')
                ->with('comments')
                // ->with('user.profile:user_id,phone_country_code,phone,mobile_country_code,mobile') 
                ->withTrashed()
                ->firstOrFail();
        }

        $data = compact(
            'building'
        );

        $data['data_id'] = $building_id;

        return response()->json([
            'error' => '',
            'data' => $data
        ], 200);

    }
    

}
