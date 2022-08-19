<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Requests\StoreRetailDealRequest;
use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Support\Facades\Route;

use App\Models\RetailStore;
use App\Models\RetailDeal;
use App\Models\RetailDealRedeems;

use App\Models\Building;

use DataTables;
use DB;
use Auth;

use Carbon\Carbon;
use Illuminate\Support\Arr;

use App\Traits\FileManager;


class RetailDealController extends Controller
{

    use FileManager;

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
     * Display the Retail Deals List
     *
     * @return Response
     */
    public function index($tab = null) {
        return view(Route::currentRouteName(), compact('tab'));
    }



    /**
     *  Retail Deal single page view
     *
     * @return Response
     */
    public function show($tab = null) {
        return view(Route::currentRouteName(), compact('tab'));
    }



    /**
     * Get a deal by id
     * @return Response
     */
    public function get($deal_id) {
   
        $deal = RetailDeal::where('id', $deal_id)
                    ->with('store:id,name,thumb')
                    ->ownOnly()
                    ->first();

        if(!$deal) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Deal']),
                'data' => []
            ], 400);
        }

        $deal->update_route = route('app.deal.store', ['store_id' => $deal->store_id, 'deal_id' => $deal->id]);
        $deal->thumb_path = $deal->getThumb('820x500');

        return response()->json([
            'error' => '',
            'data' => $deal
        ], 200);
    }



    /**
     * Create or Update a Retail Deal
     *
     * @param  App\Http\Requests\StoreRetailDealRequest $request
     * @param  int $store_id
     * @param  int $deal_id || 0
     * @return Response/Json
     */
    public function store(StoreRetailDealRequest $request, $store_id, $deal_id = 0) {
        
        $is_new = $deal_id == 0 ? true : false;

        $store = RetailStore::where('id', $store_id)
            ->ownOnly()
            ->first();

        // the retail store not found
        if( !$store ) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Retail Store']),
                'data' => []
            ], 400);
        }

        // Store
        $deal = $store->deals()->updateOrCreate(['id' => $deal_id], $request->all());

        // Store the thumbnail
        if($request->thumb) {

            $thumb_name = $this->uploadInlineThumbnail($request->thumb, $deal->imagePath());

            if($thumb_name) {
                $deal->thumb = $thumb_name;
                $deal->save();
            }
        }

        // On deal update: Check if the thumb has been removed
        if($is_new == false && (!$request->thumb && !$request->filepond)) {

            $is_removed = $this->removeInlineThumbnail($deal->imagePath(), $deal->thumb);

            if($is_removed) {
                $deal->thumb = NULL;
                $deal->save();
            }
        }

        if( !$deal ) {
            return response()->json([
                'error' => $deal_id == 0 ? __('messages.cannotSave', ['type' => 'Deal']) : __('messages.notFound', ['type' => 'Deal']),
                'data' => []
            ], 400);
        }

        return response()->json([
            'error' => '',
            'message' => $deal_id == 0 ? __('messages.created', ['type' => 'Deal']) : __('messages.updated', ['type' => 'Deal']),
            'data' => [
                'id' => $deal->id
            ]
        ], 200);
    }



    /**
     * Clone a retail deal
     */
    public function clone($deal_id) {

        $deal = RetailDeal::where('id', $deal_id)
                    ->ownOnly()
                    ->first();

        if(!$deal) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Deal']),
                'data' => []
            ], 400);
        }

        $newDeal = $deal->replicate();

        $newDeal->title = $deal->title.' - Copy';
        $newDeal->status = RetailDeal::$STATUS_INACTIVE;
        $newDeal->thumb = NULL;
        $newDeal->save();

        return response()->json([
            'error' => '',
            'message' => __('messages.cloned', ['type' => 'Deal']),
            'data' => ['id' => $newDeal->id]
        ], 200);
    }



    /**
     * Soft delete the a store and its deals
     *
     * @param  Illuminate\Http\Request $request
     * @param  int $deal_id
     * @return Response/Json
     */
    public function destroy(Request $request, $deal_id) {

        $deal = RetailDeal::where('id', $deal_id)
            ->ownOnly()
            ->first();

        if( ! $deal ) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Deal']),
                'data' => []
            ], 400);
        }

        // Soft delete
		$deal->deleted_at = Carbon::now()->toDateTimeString();
		$deal->status = RetailDeal::$STATUS_DELETED;
        $deal->save();
        
        return response()->json([
            'error' => '',
            'message' => __('messages.deleted', ['type' => 'Deal']),
            'data' => []
        ], 200);
    }



    /**
     * List Deals and return in DataTables.
     *
     * @param  Request $request
     * @return DataTables/Json
     */

    public function list(Request $request, $tab = null)
    {

        $items = RetailDeal::select(DB::raw("
            retail_deals.id,
            retail_deals.title,
            retail_deals.subtitle,
            retail_deals.thumb,

            retail_stores.id as store_id,
            retail_stores.name as store_name,
            retail_stores.thumb as store_thumb,

            buildings.id as building_id,
            buildings.name as building_name,
            buildings.suburb,
            buildings.is_thumb as building_thumb,

            retail_deals.allowed_redeem_num,
            retail_deals.status,
            retail_deals.thumb,
            retail_deals.created_at
        "))
        ->leftJoin('retail_stores', 'retail_stores.id', 'retail_deals.store_id')
        ->leftJoin('buildings', 'buildings.id', 'retail_stores.building_id')
        ->OwnOnly();

        // Archive tab
        if($tab == 'archive') {
            $items = $items->onlyTrashed();
        }

        if($request->building_id) {
            $items = $items->where('retail_stores.building_id', $request->building_id); 
        }

        if($request->store_id) {
            $items = $items->where('store_id', $request->store_id); 
        }
        
        $JSON = DataTables::of($items)
            
            ->addColumn('deal', function(RetailDeal $deal) {
                return '
                    <span class="row-col title open-data" data-open-deal="'.$deal->id.'">
                        '.$deal->thumbOrInitials().'
                        <span>'.$deal->title.'</span>
                        <small>'.$deal->subtitle.'</small>
                    </span>';
            })

            ->addColumn('store', function(RetailDeal $deal) {
                return '<span>'.$deal->store_name.'</span>';
            })

            ->addColumn('building', function(RetailDeal $deal) {

                if(!$deal->building_name) 
                    return '';

                return '
                    <div class="row-col">
                        '.Building::getThumbOrInitials($deal->building_id, $deal->building_name, $deal->building_thumb).'
                        <span>'.$deal->building_name.'</span>
                        <small data-exclude="true">'.$deal->suburb.'</small>
                    </div>';
            })

            ->addColumn('redeem_no', function(RetailDeal $deal) {
                if(!$deal->allowed_redeem_num) {
                    return 'Unlimited';
                }
                return $deal->allowed_redeem_num;
            })

            ->addColumn('status', function(RetailDeal $deal) {
                return $deal->getStatus();
            })

            ->addColumn('created_date', function(RetailDeal $deal) {
                return '
                <strong>'.dateFormat($deal->created_at).'</strong><br>
                <small>'.timeFormat($deal->created_at).'</small>';
            })

            ->addColumn('actions', function(RetailDeal $deal) {
                
                $actions = '<div class="btn-hspace">
                            <button type="button" data-toggle="dropdown" class="btn btn-sm btn-i btn-primary btn-simple" aria-expanded="true">
                                Actions <i class="material-icons">expand_more</i>
                            </button>
                            <ul role="menu" class="dropdown-menu dropdown-menu-right">';
                            // Clone
                            $actions .= '<li>
                                <form action="'.route('app.deal.clone', $deal->id).'" method="POST">
                                    '.csrf_field().'<button type="submit" class="no-btn">Clone</button>
                                </form>
                            </li>';
                            // Delete
                            if( Auth::user()->canDelete() ) {
                                $actions .= '<li><a class="actions" data-target="#mod-delete" type="button" href="'.route('app.deal.delete', $deal->id).'">Delete</a></li>';
                            }
                            $actions .= '
                                </ul>
                        </div>';

                return $actions;
            })

            /** 
             * Column Filer
             */
            ->filterColumn('deal', function($query, $keyword) {
                $query->whereRaw("retail_deals.title like ?", ["%{$keyword}%"])
                      ->orWhereRaw("retail_deals.subtitle like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('store', function($query, $keyword) {
                $query->whereRaw("retail_deals.allowed_redeem_num like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('building', function($query, $building_id) {
                $query->where("buildings.id", $building_id);
                return;
            })
            ->filterColumn('status', function($query, $status) {
                $query->where("retail_deals.status", $status);
                return;
            })

            /** 
             * Decode HTML chars
             */
            ->rawColumns([
                'deal', 
                'store', 
                'building', 
                'redeem_no', 
                'status', 
                'created_date',
                'actions'
            ])

            /** 
             * Column Order
             */
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
                    case 'id':
                        $query->orderBy("retail_deals.id", $dir);
                        break;
                    case 'deal':
                        $query->orderBy("retail_deals.title", $dir);
                        break;
                    case 'store':
                        $query->orderBy("retail_stores.name", $dir);
                        break;
                    case 'building':
                        $query->orderBy("buildings.name", $dir);
                        break;
                    case 'redeem_no':
                        $query->orderBy("retail_deals.allowed_redeem_num", $dir);
                        break;
                    case 'status':
                        $query->orderBy("status", $dir);
                        break;
                    case 'created_date':
                        $query->orderBy("created_at", $dir);
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
                    return exportToCSV($data_to_export, 'Retail Deals Export '.Carbon::now()->format('md_His').'.csv');
                }
            }

            return $JSON->toJson();
    }



    /**
     * Redeem history list
     * 
     * @param  Request $request
     * @return DataTables/Json
     */
    public function listRedeemHistory(Request $request, $store_id)
    {

        $items = RetailDealRedeems::select(DB::raw("
            user_deal_redeems.code,
            user_deal_redeems.created_at,

            retail_deals.id as deal_id,
            retail_deals.store_id as deal_store_id,
            retail_deals.title as deal_title,
            retail_deals.subtitle as deal_subtitle,
            retail_deals.thumb as deal_thumb,

            concat(users.first_name, ' ', users.last_name) as user_name,
            users.email as user_email
        "))
        ->leftJoin('retail_deals', 'retail_deals.id', 'user_deal_redeems.retail_deal_id')
        ->leftJoin('users', 'users.id', 'user_deal_redeems.user_id')
        ->where('retail_deals.store_id', $store_id);

        $JSON = DataTables::of($items)
            ->addColumn('code', function(RetailDealRedeems $redeem) {
                return '<span class="_id">'.$redeem->code.'</span>';
            })
            ->addColumn('deal', function(RetailDealRedeems $redeem) {
                $a = '<span class="row-col title">
                        <b>'.$redeem->deal_title.'</b>';
                        if($redeem->deal_subtitle) {
                            $a .= '<small>'.$redeem->deal_subtitle.'</small>';
                        }
                $a .= '</span>';
                return $a;
            })
            ->addColumn('resident', function(RetailDealRedeems $redeem) {
                return '
                <span class="row-col title">
                    <b>'.$redeem->user_name.'</b>
                    <small data-exclude="true">'.$redeem->user_email.'</small>
                </span>';
            })
            ->addColumn('date', function(RetailDealRedeems $redeem) {
                return '
                <strong>'.dateFormat($redeem->created_at).'</strong><br>
                <small>'.timeFormat($redeem->created_at).'</small>';
            })

            /** 
             * Column Filer
             */
            ->filterColumn('code', function($query, $keyword) {
                $query->whereRaw("user_deal_redeems.code like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('deal', function($query, $keyword) {
                $query->whereRaw("retail_deals.title like ?", ["%{$keyword}%"])
                      ->orWhereRaw("retail_deals.subtitle like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('resident', function($query, $building_id) {
                $query->whereRaw("concat(users.first_name, ' ', users.last_name) ", ["%{$keyword}%"])
                      ->orWhereRaw("users.email like ?", ["%{$keyword}%"]);
                return;
            })

            /** 
             * Decode HTML chars
             */
            ->rawColumns(['code', 'deal', 'resident', 'date'])

            /** 
             * Order
             */
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
                    case 'code':
                        $query->orderBy("user_deal_redeems.code", $dir);
                        break;
                    case 'deal':
                        $query->orderBy("retail_deals.title", $dir);
                        break;

                    case 'resident':
                        $query->orderBy("users.first_name", $dir);
                        break;

                    case 'date':
                        $query->orderBy("user_deal_redeems.created_at", $dir);
                        break;
                }
            });

            // Export
            if( isset($request->action) ) {

                $data = $JSON->toArray();
            
                // Included columns
                $listed_columns = Arr::pluck($data['input']['columns'], 'name');

                // Filter out the unwanted ones
                $except = [];

                $listed_columns = array_diff($listed_columns, $except);

                $add_to_export = [
                    'user_email'
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
                    return exportToCSV($data_to_export, 'Retail Deal Redeem History Export '.Carbon::now()->format('md_His').'.csv');
                }
            }


            return $JSON->toJson();
    }


}
