<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Arr;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests\StoreBuildingRequest;

use App\Models\Pivot\BuildingUser;

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
        $this->middleware('role:super-admin|building-manager|admin');
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
    public function show($tab = null) {
        return view(Route::currentRouteName(), compact('tab'));
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



    /**
     * Get the list of Buildings
     *
     * @param Request
     * @return Response/Json
     */
    public function getBuildings(Request $request)
    {
        if(!$request->term) {
            $Buildings = Building::orderByRaw("name ASC")
                ->myBuildings()
                ->limit(10)
                ->get([
                    'id',
                    'name',
                    'street_address_1',
                    'street_address_2',
                    'suburb',
                    'postcode',
                    'state',
                    'phone',
                    'is_thumb'
                ]);
        }
        else {
            $Buildings = Building::whereRaw("name like '%{$request->term}%'")
                ->orderByRaw("name ASC")
                ->myBuildings()
                ->limit(10)
                ->get([
                    'id',
                    'name',
                    'street_address_1',
                    'street_address_2',
                    'suburb',
                    'postcode',
                    'state',
                    'phone',
                    'is_thumb'
                ]);
        }

        foreach($Buildings as $building) {
            if($building->is_thumb) {
                $building->is_thumb = $building->getThumb();
            }
        }

        $all_option = [
            'id'    => 'all',
            'name'  =>  'All',
            'street_address_1' => 'All buildings',
            'street_address_2',
            'suburb',
            'postcode',
            'state',
            'phone',
            'is_thumb' 
        ];

        return json_encode($Buildings);
    }



    /**
     * Create or Update a Building
     *
     * @param  Illuminate\Http\StoreBuildingRequest $request
     * @param  int $building_id
     * @return Response
     */
    public function store(StoreBuildingRequest $request, $building_id = 0)
    {
        // Process the office hours -> collect and create array
        $office_hours = _jsonOfficeHours($request->all());

        // Store / Update
        $Building = Building::withTrashed()->updateOrCreate(['id' => $building_id], $request->all() + ['office_hours' => $office_hours]);

        // Building not found
        if( !$Building ) {
            return response()->json([
                'error' => $building_id == 0 ? __('messages.cannotSave', ['type' => 'Building']) : __('messages.notFound', ['type' => 'Building']),
                'data' => []
            ], 400);
        }

        return response()->json([
            'error' => '',
            'message' => $building_id == 0 ? __('messages.created', ['type' => 'Building']) : __('messages.updated', ['type' => 'Building']),
            'data' => [
                'id' => $Building->id
            ]
        ], 200);
    }



    /**
     * Store the Building Page's Content
     * 
     * @param Request $request
     * @param int $building_id
     * @return Response/Json
     */
    public function storePage(Request $request, $building_id) {

        $Building = Building::withTrashed()->where('id', $building_id)->first();

        if(!$Building) {
            return response()->json([
                'error' => 'Building not found',
                'data' => []
            ], 400);
        }

        $Building->page_content()->updateOrCreate([], [
            'content' => $request->building_content
        ]);

        return response()->json([
            'error' => '',
            'message' => __('messages.updated', ['type' => 'Building Page']),
            'data' => [
                'id' => $Building->id
            ]
        ], 200);

    }



    /**
     * Soft delete a building
     *
     * @param  Illuminate\Http\Request $request
     * @param  int $building_id
     * @return Response
     */
    public function destroy(Request $request, $building_id)
    {
        if( ! Auth::user()->isSuperAdmin() ) {
            return response()->json([
                'error' => '',
                'message' => 'No permission to proceed this action',
                'data' => []
            ], 200);
        }

        $building = Building::where('id', $building_id)->first();

        if( ! $building ) {
            return response()->json([
                'error' => '',
                'message' => __('messages.notFound', ['type' => 'Building']),
                'data' => []
            ], 200);
        }

        // set all user relation to this building inactive
        BuildingUser::where('building_id', $building->id)->update([
            'relation_status' => 0, // inactive
            'relation_end' => Carbon::now()
        ]);

        // Soft delete
        $building->delete();

        // Return for ajax.
        return response()->json([
            'error' => '',
            'message' => __('messages.deleted', ['type' => 'Building']),
            'data' => []
        ], 200);
    }



    /**
     * List Buildings and return in DataTables.
     *
     * @param  Illuminate\Http\Request $request
     * @return DataTables
     */

    public function List(Request $request)
    {

        $items = Building::select(DB::raw("
            buildings.id,
            buildings.name as name,
            buildings.is_thumb,
            buildings.suburb,
            GROUP_CONCAT(concat(users.first_name, ' ', users.last_name)) as managers,

            (SELECT
                COUNT(bookings.id)
                FROM bookings
                WHERE bookings.building_id = buildings.id
                AND bookings.status IN (1,2)
            ) AS bookings_total,

            (SELECT
                COUNT(building_user.user_id)
                FROM building_user
                WHERE building_user.building_id = buildings.id
                AND building_user.relation_status = ".BuildingUser::$STATUS_ACTIVE."
                AND building_user.relation_type = ".BuildingUser::$RELATION_TYPE_RESIDENT."
            ) AS residents_num
        "))
        ->leftJoin('building_user', function($join) {
            $join->on('building_user.building_id', '=', 'buildings.id');
            $join->on('building_user.relation_status', '=', DB::raw(true));
            $join->on('building_user.relation_type', '=', DB::raw(BuildingUser::$RELATION_TYPE_MANAGEMENT));
        })
        ->leftJoin('users', 'users.id', '=', 'building_user.user_id')
        ->groupBy('buildings.id');

        $JSON = DataTables::of($items)
            // Name
            ->addColumn('name', function(Building $building) {
                return '<a href="'.route('app.building.show', $building->id).'" class="row-col title">
                            '.$building->ThumbOrInitials().'
                            <span>'.$building->name.'</span>
                            <small data-exclude="true">'.$building->suburb.'</small>
                        </a>';
            })
            // Building Managers
            ->addColumn('managers', function(Building $building) {
                if(!$building->managers) {
                    return '-';
                }
                $managers = explode(',' ,$building->managers);
                $r = '';
                foreach($managers as $mg) {
                    $r .= '<span class="label">'.$mg.'</span>';
                }
                return $r;
            })
            // Residents No.
            ->addColumn('residents_num', function(Building $building) {
                if($building->residents_num == 0) {
                    return '<span>-</span>';
                }
                return '<span>'.$building->residents_num.' '.str_plural('resident', $building->residents_num).'</span>';
            })
            // Booking Total
            ->addColumn('bookings_total', function(Building $building) {
                if(!$building->bookings_total) {
                    return '<small>No active bookings</small>';
                }
                return '<span>'.$building->bookings_total.' '.str_plural('booking', $building->bookings_total).'</span>';
            })
            // Actions
            ->addColumn('actions', function(Building $building) {
                return '
                <div class="btn-hspace">
                    <button type="button" data-toggle="dropdown" class="btn btn-sm btn-i btn-primary btn-simple" aria-expanded="true">
                        Actions <i class="material-icons">expand_more</i>
                    </button>
                    <ul role="menu" class="dropdown-menu dropdown-menu-right">
                        <li><a href="'.route('app.preview.building', $building->id).'" target="_blank">Preview</a></li>
                        <li><a class="actions" data-target="#mod-delete" type="button" href="'.route('app.building.delete', $building->id).'">Delete</a></li>
                    </ul>
                </div>';
            })
            /** 
             * Column Filer
             *
             */
            ->filterColumn('name', function($query, $keyword) {
                $query->whereRaw("buildings.name like ?", ["%{$keyword}%"])
                      ->orWhereRaw("buildings.suburb like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('managers', function($query, $keyword) {
                $query->whereRaw("concat(users.first_name, ' ', users.last_name) like ?", ["%{$keyword}%"]);
                return;
            })
            // Decode HTML chars
            ->rawColumns([
                'name', 
                'managers', 
                'residents_num', 
                'bookings_total', 
                'actions'
            ])
        
            // Column Order
            ->order(function ($query) {     

                $dir = 'asc';
                $order = request()->input('order');
                $order_by = null;
                
                if($order) {
                    $col_inx = $order[0]['column'];
                    $order_by = request()->input('columns')[$col_inx]['data'];
                    $dir = $order[0]['dir'];
                }
                switch($order_by) {
                    case 'name':
                        $query->orderBy("buildings.name", $dir);
                        break;
                    case 'residents_num':
                        $query->orderBy("residents_num", $dir);
                        break;
                    case 'bookings_total':
                        $query->orderBy("bookings_total", $dir);
                        break;
                }
            });

            // Export
            if( isset($request->action) ) {

                $data = $JSON->toArray();
              
                // Included columns
                $listed_columns = Arr::pluck($data['input']['columns'], 'name');

                // Filter out the unwanted ones
                $except = [
                    'actions'
                ];

                $listed_columns = array_diff($listed_columns, $except);

                $add_to_export = [
                    'suburb',
                ];

                $listed_columns = array_merge($listed_columns, $add_to_export);

                // do filtering
                foreach($data['data'] as $key => $array) {
                    $data['data'][$key] = Arr::only($array, $listed_columns);
                }
                
                // remove HTML tags

                $data_to_export = collect($data['data'])->map(function ($row) {
                    return collect($row)->mapWithKeys(function ($value, $key) {
                            return [$key => cleanHtmlToExport($value)];
                    })->all();
                })->all();
      

                // Export to CSV 
                if($request->action == 'csv') {
                    return exportToCSV($data_to_export, 'Bookings Export '.Carbon::now()->format('md_His').'.csv');
                }
            }

            
            // return for ajax
            return $JSON->toJson();
    }

}
