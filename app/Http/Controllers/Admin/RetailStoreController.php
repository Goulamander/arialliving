<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\StoreRetailStoreRequest;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Support\Facades\Route;

use App\Models\RetailStore;
use App\Models\Building;

use DataTables;
use DB;
use Auth;

use Carbon\Carbon;
use Illuminate\Support\Arr;


class RetailStoreController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super-admin|building-manager|admin|external');
    }



    /**
     * Display the Retail Store List
     *
     * @return Response
     */
    public function index($tab = null) {
        return view(Route::currentRouteName(), compact('tab'));
    }



    /**
     * Retail Store single page view
     *
     * @return Response
     */
    public function show() {
        $tab = 'deals';
        return view(Route::currentRouteName(), compact('tab'));
    }

    public function showRedeemHistory() {
        $tab = 'redeem-history';
        return view('app.store.show', compact('tab'));
    }



    /**
     * Create or Update a Retail Store
     *
     * @param  Illuminate\Http\StoreRetailStoreRequest $request
     * @param  int $store_id || 0
     * @return Response/Json
     */

    public function store(StoreRetailStoreRequest $request, $store_id = 0) {

        $is_new = $store_id == 0 ? true : false;

        $data = $request->all();

        if(!$request->user_id) {
            $data = $request->except('user_id');
        }

        $store = RetailStore::updateOrCreate(['id' => $store_id], $data);

        if(!$is_new && !$request->user_id) {
            $store->user_id = NULL;
            $store->save();
        }

        // Store not found
        if( !$store ) {
            return response()->json([
                'error' => $store_id == 0 ? __('messages.cannotSave', ['type' => 'Retail Store']) : __('messages.notFound', ['type' => 'Retail Store']),
                'data' => []
            ], 400);
        }

        return response()->json([
            'error' => '',
            'message' => $store_id == 0 ? __('messages.created', ['type' => 'Retail Store']) : __('messages.updated', ['type' => 'Retail Store']),
            'data' => [
                'id' => $store->id
            ]
        ], 200);
    }



    /**
     * Soft delete the a store and its deals
     *
     * @param  Illuminate\Http\Request $request
     * @param  int $store_id
     * @return Response
     */
    public function destroy(Request $request, $store_id) {

        $store = RetailStore::deleteStore($store_id);

        return response()->json([
            'error' => '',
            'message' => __('messages.deleted', ['type' => 'Retail Store']),
            'data' => []
        ], 200);
    }



    /**
     * List Stores and return in DataTables.
     *
     * @param  Illuminate\Http\Request $request
     * @return DataTables
     */

    public function list(Request $request, $tab = null)
    {

        $items = RetailStore::select(DB::raw("
            retail_stores.id,
            retail_stores.name,
            retail_stores.thumb,
            retail_stores.status,
            retail_stores.user_id,

            concat(users.first_name, ' ', users.last_name) as user_name,
          
            buildings.id as building_id,
            buildings.name as building_name,
            buildings.suburb,
            buildings.is_thumb as building_thumb,

            (SELECT count(*) FROM retail_deals WHERE store_id = retail_stores.id AND status = 1) AS deals_no
        "))
        ->leftJoin('buildings', 'buildings.id', '=', 'retail_stores.building_id')
        ->leftJoin('users', 'users.id', 'retail_stores.user_id')
        ->OwnOnly()
        ->groupBy('retail_stores.id');

        // Archive tab
        if($tab == 'archive') {
            $items = $items->onlyTrashed();
        }

        if($request->building_id) {
            $items = $items->where('building_id', $request->building_id); 
        }
        
        $JSON = DataTables::of($items)
            
            ->addColumn('name', function(RetailStore $store) {
                return '
                    <a href="'.route('app.store.show', $store->id).'" class="row-col title">
                        '.$store->thumbOrInitials().'
                        <span>'.$store->name.'</span>
                    </a>';
            })

            ->addColumn('building', function(RetailStore $store) {
                
                if(!$store->building_name) 
                    return '';

                return '
                    <div class="row-col">
                        '.Building::getThumbOrInitials($store->building_id, $store->building_name, $store->building_thumb).'
                        <span>'.$store->building_name.'</span>
                        <small data-exclude="true">'.$store->suburb.'</small>
                    </div>';
            })

            ->addColumn('store_manager', function(RetailStore $store) {
                if(!$store->user_id) {
                    return '-';
                }
                return $store->user_name;
            })

            ->addColumn('deals_no', function(RetailStore $store) {
                if($store->deals_no == 0) {
                    return '-';
                }
                return '<label class="label">'.$store->deals_no.' '.str_plural('deals', $store->deals_no).'</label>';
            })

            ->addColumn('status', function(RetailStore $store) {
                return $store->getStatus();
            })

            ->addColumn('actions', function(RetailStore $store) {
                
                $actions = '<div class="btn-hspace">
                            <button type="button" data-toggle="dropdown" class="btn btn-sm btn-i btn-primary btn-simple" aria-expanded="true">
                                Actions <i class="material-icons">expand_more</i>
                            </button>
                            <ul role="menu" class="dropdown-menu dropdown-menu-right">';
                            // Delete
                            if( Auth::user()->canDelete() ) {
                                $actions .= '<li><a class="actions" data-target="#mod-delete" type="button" href="'.route('app.store.delete', $store->id).'">Delete</a></li>';
                            }
                            $actions .= '
                                </ul>
                        </div>';

                return $actions;
            })
            /** 
             * Column Filer
             *
             */
            ->filterColumn('name', function($query, $keyword) {
                $query->whereRaw("retail_stores.name like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('building', function($query, $building_id) {
                $query->where("retail_stores.building_id", $building_id);
                return;
            })
            ->filterColumn('store_manager', function($query, $keyword) {
                $query->whereRaw("concat(users.first_name, ' ', users.last_name) like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('status', function($query, $status) {
                $query->where("retail_stores.status", $status);
                return;
            })
            // Decode HTML chars
            ->rawColumns([
                'name', 
                'building', 
                'store_manager', 
                'deals_no', 
                'status', 
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
                        $query->orderBy("name", $dir);
                        break;
                    case 'building':
                        $query->orderBy("buildings.name", $dir);
                        break;
                    case 'deals_no':
                        $query->orderBy("deals_no", $dir);
                        break;
                    case 'status':
                        $query->orderBy("status", $dir);
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

                $add_to_export = [];

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
                    return exportToCSV($data_to_export, 'Retail Stores Export '.Carbon::now()->format('md_His').'.csv');
                }
            }

            // return for ajax
            return $JSON->toJson();

    }


}
