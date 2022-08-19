<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseClasses\Controller;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\StoreResidentRequest;
use App\Http\Requests\StoreMarketingCommunicationsRequest;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

use App\Models\Activation;
use App\Services\ActivationService;

use App\Models\User;
use App\Models\Role;
use App\Models\Building;
use App\Models\Pivot\BuildingUser;
use App\Models\UserSetting;
use App\Models\MarketingCommunications;

use DataTables;

use DB;
use Auth;

use Carbon\Carbon;
use Illuminate\Support\Arr;


class MarketingCommunicationsController extends Controller
{

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:super-admin|building-manager|admin');
    }



    /**
     * Display the User List
     *
     * @return Response
     */
    public function index($tab = null) {
        return view(Route::currentRouteName(), compact('tab'));
    }



    /**
     * User single page view
     *
     * @return Response
     */
    public function show($tab = null) {
        return view(Route::currentRouteName(), compact('tab'));
    }

    /**
     * get Resident List.
     *
     * @param  App\Http\Requests\Request $request
     * @return Response
     */
    public function getResidentList(Request $request) {   
        // No permission
        if( ! Auth::user()->isSuperAdmin() ) {
            return response()->json([
                'error' => 'You have no permission to create new users',
                'data' => [],
            ], 400);
        }

        if($request->has('resident_levels')){
            // Validate the Role id
            $role = Role::where('id', $request->resident_levels)->first(['id', 'name', 'display_name']);
            
            if( !$role ) {
                return response()->json([
                    'error' => 'The provided Role Id is invalid.',
                    'message' => '',
                    'data' => []
                ], 400);
            }
            $residents = User::select('id', 'first_name', 'last_name')
					->where('role_id', $role->id)
					->get()
					->toArray();
            return response()->json([
                'error' => '',
                'message' => '',
                'data' => $residents,
            ], 200);

        }

        if($request->has('building_id')){
            $building = Building::where('id', $request->building_id)->first();
            
            if( !$building ) {
                return response()->json([
                    'error' => 'The provided building Id is invalid.',
                    'message' => '',
                    'data' => []
                ], 400);
            }
            $residents = $building->users()->select('id', 'first_name', 'last_name')
					->get()
                    ->toArray();
            return response()->json([
                'error' => '',
                'message' => '',
                'data' => $residents,
            ], 200);

        }
    }


    /**
     * Create a new user (admin).
     *
     * @param  App\Http\Requests\StoreMarketingCommunicationsRequest $request
     * @return Response
     */
    public function create(StoreMarketingCommunicationsRequest $request) {
        // No permission
        if( ! Auth::user()->isSuperAdmin() ) {
            return response()->json([
                'error' => 'You have no permission to create new users',
                'data' => [],
            ], 400);
        }

        // Create the new user
        $_save_data = $request->all();
        if($request->has('receiver')){
            $_save_data['receiver'] = implode (",", $request->receiver);
        }
        $data = MarketingCommunications::create($_save_data);

        // send email or sms
        if($request->status == MarketingCommunications::$STATUS_SEND) {
            if($request->send_via == MarketingCommunications::$SEND_VIA_EMAIL){
                $data->sendEmail();
            }
            if($request->send_via == MarketingCommunications::$SEND_VIA_SMS){
                $data->sendSms();
            }
        }

        return response()->json([
            'error' => '',
            'message' => 'Added successfully.',
            'data' => [
                'id' => $data->id
            ]
        ], 200);
    }



    /**
     * Update a user (admin)
     *
     * @param  App\Http\Requests\StoreMarketingCommunicationsRequest $request
     * @return Response
     */
    public function update(StoreMarketingCommunicationsRequest $request, $id) {
        // No permission
        if( ! Auth::user()->isSuperAdmin() ) {
            return response()->json([
                'error' => 'You have no permission to create new users',
                'data' => [],
            ], 400);
        }

        // do user update
        $model = MarketingCommunications::where('id', $id)
            ->withTrashed()
            ->first();

        $_save_data = $request->only($model->getFillable());
        if($request->has('receiver')){
            $_save_data['receiver'] = implode (",", $request->receiver);
        }
            
        $model->update($_save_data);

        // send email or sms
        if($request->status == MarketingCommunications::$STATUS_SEND) {
            if($request->send_via == MarketingCommunications::$SEND_VIA_EMAIL){
                $model->sendEmail();
            }
            if($request->send_via == MarketingCommunications::$SEND_VIA_SMS){
                $model->sendSms();
            }
        }

        return response()->json([
            'error' => '',
            'message' => 'Updated successfully.',
            'data' => [
                'id' => $model->id
            ]
        ], 200);
    }

    /**
     * List Residents and return in DataTables.
     *
     * @param  Illuminate\Http\Request $request
     * @return DataTables
     */

    public function list(Request $request, $tab = null)
    {
        $items = MarketingCommunications::select(['id', 'subject', 'send_via', 'status', 'created_at']);

        // Archive tab
        if($tab == 'archive') {
            $items = $items->onlyTrashed();
        }

        $JSON = DataTables::of($items)
            ->addColumn('subject', function(MarketingCommunications $item) {
                return '<a href="'.route('app.marketing-communications.show', $item->id).'" class="row-col title">
                            <span>'.$item->subject.'</span>
                        </a>';
            })
            ->addColumn('status', function(MarketingCommunications $item) {
                return $item->getStatus();
            })
            ->addColumn('send_via', function(MarketingCommunications $item) {
                return $item->getSendVia();
            })
            ->addColumn('created_at', function(MarketingCommunications $item) {
                return $item->created_at;
            })
            // Actions
            ->addColumn('actions', function(MarketingCommunications $item) {
                
                $actions = '<div class="btn-hspace">
                            <button type="button" data-toggle="dropdown" class="btn btn-sm btn-i btn-primary btn-simple" aria-expanded="true">
                                Actions <i class="material-icons">expand_more</i>
                            </button>
                            <ul role="menu" class="dropdown-menu dropdown-menu-right">';
                            if( Auth::user()->canDelete() ) {
                                $actions .= '<li><a class="actions" data-target="#mod-delete" type="button" href="'.route('app.marketing-communications.delete', $item->id, false).'">Delete</a></li>';
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
            ->filterColumn('id', function($query, $keyword) {
                $query->where("id", $keyword);
                return;
            })
            ->filterColumn('subject', function($query, $keyword) {
                $query->whereRaw("subject like ?", ["%{$keyword}%"]);
                return;
            })
            // Decode HTML chars
            ->rawColumns([
                'subject', 
                'status', 
                'send_via', 
                'created_at', 
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
                    case 'id':
                        $query->orderBy('id', $dir);
                        break;
                    case 'subject':
                        $query->orderBy("subject", $dir);
                        break;
                    case 'send_via':
                        $query->orderBy("send_via", $dir);
                        break;
                    case 'status':
                        $query->orderBy("status", $dir);
                        break;
                }
            });
          
            // return for ajax
            return $JSON->toJson();
           
    }

    /**
     * Soft delete (Cancel)
     *
     * @param  Illuminate\Http\Request $request
     * @param  int $booking_id
     * @return Response/Json
     */
    public function destroy(Request $request, $id)
    {
        $data = MarketingCommunications::find($id);

        if(!$data) {
            // can not found building
            return response()->json([
                'error' => 'Not found, or you have no permission to proceed with this action',
                'data' => [],
            ], 400);
        }

        // init the cancel flow
        $data->delete();

        //
        return response()->json([
            'error' => '',
            'data' => compact(['data']),
            'message' => 'Deleted.',
        ], 200);
    }

}
