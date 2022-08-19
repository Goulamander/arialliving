<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseClasses\Controller;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\StoreResidentRequest;

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

use DataTables;

use DB;
use Auth;

use Carbon\Carbon;
use Illuminate\Support\Arr;


class UserController extends Controller
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
     * Create a new user (admin).
     *
     * @param  App\Http\Requests\StoreUserRequest $request
     * @return Response
     */
    public function create(StoreUserRequest $request) {   

        // No permission
        if( ! Auth::user()->isSuperAdmin() ) {
            return response()->json([
                'error' => __('messages.noPermission'),
                'data' => [],
            ], 400);
        }

        // Validate the Role id
        $role = Role::where('id', $request->role_id)->first(['id', 'name', 'display_name']);
        
        if( !$role ) {
            return response()->json([
                'error' => __('messages.invalidData', ['type' => 'Role']),
                'message' => '',
                'data' => []
            ], 400);
        }

        // Create the new user
        $user = User::create($request->all());

        // Send invitation to the Admin
        if($request->invite_user) {
            $activation = new Activation();
            $activation->createActivation($user);
            
            $activationService = new ActivationService($activation);
            $activationService->sendActivationMail($user, false);
        }

        return response()->json([
            'error' => '',
            'message' => __('messages.created', ['type' => $role->display_name]),
            'data' => [
                'id' => $user->id
            ]
        ], 200);
    }



    /**
     * Update a user (admin)
     *
     * @param  App\Http\Requests\StoreUserRequest $request
     * @return Response
     */
    public function update(StoreUserRequest $request, $user_id) {
               
        // No permission
        if( ! Auth::user()->isSuperAdmin() ) {
            return response()->json([
                'error' => __('messages.noPermission'),
                'data' => [],
            ], 400);
        }

        // Validate the Role id
        $role = Role::where('id', $request->role_id)->first(['id', 'name', 'display_name']);
        
        if( !$role ) {
            return response()->json([
                'error' => __('messages.invalidData', ['type' => 'Role']),
                'message' => '',
                'data' => []
            ], 400);
        }

        // do user update
        $user = User::where('id', $user_id)
            ->withTrashed()
            ->first();
            
        $user->update($request->only($user->getFillable()));

        return response()->json([
            'error' => '',
            'message' => __('messages.updated', ['type' => $role->display_name]),
            'data' => [
                'id' => $user->id
            ]
        ], 200);
    }



    /**
     * Update the user (admin) building access
     * 
     * @param Request $request
     * @return Response
     */
    public function updateBuildings(Request $request, $user_id) {
       
        // No permission
        if( ! Auth::user()->isSuperAdmin() ) {
            return response()->json([
                'error' => __('messages.noPermission'),
                'data' => [],
            ], 400);
        }

        $user = User::where('id', $user_id)->first();

        if( ! $user ) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'User']),
                'message' => '',
                'data' => []
            ], 400);
        }

        $buildings_to_sync = [];

        if($request->buildings) {
            foreach ($request->buildings as $building_id) {
                $buildings_to_sync[$building_id] = [
                    'relation_status' => 1,
                    'relation_type'   => $user->isStaff() ? BuildingUser::$RELATION_TYPE_STAFF : BuildingUser::$RELATION_TYPE_MANAGEMENT,
                    'relation_start'  => Carbon::now()->format('Y-m-d'),
                    'notes' => 'Updated by '.Auth::user()->fullName().'. '.Carbon::now()->format('M d, Y h:i a')
                ];  
            }
        }

        $user->buildings()->sync($buildings_to_sync);

        return response()->json([
            'error' => '',
            'message' => __('messages.building.access_updated'),
            'data' => [
                'id' => $user->id
            ]
        ], 200);
    }



    /**
     * Store the resident contact details
     * 
     * @param $request
     * @return $response
     * 
     */
    public function storeNotiSetting(Request $request, $user_id) {

        $user = User::where('id', $user_id)->first();
        
        if( ! $user ) {
            return response()->json([
                'error' =>  __('messages.notFound', ['type' => 'User']),
                'message' => '',
                'data' => []
            ], 400);
        }

        $update_data = [];
        
        if(isset($request->additional_password_prompt)){
            $update_data['additional_password_prompt'] = !$request->additional_password_prompt ? UserSetting::$ADDITIONAL_PASSWORD_PROMPT_OFF : UserSetting::$ADDITIONAL_PASSWORD_PROMPT_ON;
        }
        if(isset($request->email_notification)){
            $update_data['notifications_email'] = gettype($request->email_notification) == 'array' ? implode(',',$request->email_notification) : $request->email_notification;
        }
        if(isset($request->notifications_sms)){
            $update_data['notifications_sms'] = gettype($request->notifications_sms) == 'array' ? implode(',',$request->notifications_sms) : $request->notifications_sms;
        }
        $user->settings()->updateOrCreate([], $update_data);
       
        return response()->json([
            'error' => '',
            'message' => __('messages.updated', ['type' => 'Notification Settings']),
            'data' => []
        ], 200);
    }



    /**
     * Create and store a new resident.
     *
     * @param  App\Http\Requests\StoreResidentRequest $request
     * @return Response
     */
    public function createResident(StoreResidentRequest $request) {

        // Validate the Role id
        $role = Role::where('id', $request->role_id)->first(['id', 'name', 'display_name']);
        
        if( !$role ) {
            return response()->json([
                'error' => __('messages.invalidData', ['type' => 'Resident Level']),
                'message' => '',
                'data' => []
            ], 400);
        }

        // Validate the Building Id
        $building = Building::where('id', $request->building_id)->first(['id']);
        
        if(!$building) {
            return response()->json([
                'error' => __('messages.invalidData', ['type' => 'Building']),
                'message' => '',
                'data' => []
            ], 400);
        }

        // Create the new user
        $resident = User::create($request->all());

        // Attach the building
        $resident->buildings()->attach($request->building_id, [
            'unit_no'         => $request->unit_no,
            'unit_type'       => $request->unit_type,
            'relation_start'  => $request->relation_start ? $request->relation_start : NULL, 
            'relation_end'    => $request->relation_end ? $request->relation_end : NULL,
            'relation_status' => 1, // Active
            'relation_type'   => 1, // Resident
        ]);

        // Send invitation to the Resident
        if($request->invite_resident) {
            $activation = new Activation();
            $activation->createActivation($resident);
            
            $activationService = new ActivationService($activation);
            $activationService->sendActivationMail($resident, false);
        }

        return response()->json([
            'error' => '',
            'message' => __('messages.created', ['type' => $role->display_name]),
            'data' => [
                'id' => $resident->id
            ]
        ], 200);
    }


    /**
     * Create and store a new resident.
     *
     * @param  Request $request
     * @return Response
     */
    public function createResidentFormFile(Request $request) {

        if($request->has('csv_file')){
            $residents = csvToArray($request->file('csv_file'));
            $messages = '<ul>';
            foreach($residents as $item){
                // Create the new user
                $full_name = split_full_name($item['Name']);
                $building = Building::where('name', $item['Building'])->first(['id']);
                $find_user = User::where('email', $item['Email'])->first();
                if(empty($find_user)) {
                    $user_data = [
                        'first_name' => $full_name[0],
                        'last_name' => $full_name[1],
                        'email' => $item['Email'],
                        'mobile' => $item['Mobile'],
                        'role_id' => User::$ROLE_RESIDENT,
                    ];
                    $resident = User::create($user_data);

                    // Attach the building
                    $resident->buildings()->attach($building->id, [
                        'unit_no'         => $item['Unit No'],
                        'unit_type'       => 2,
                        'relation_start'  => NULL, 
                        'relation_end'    => NULL,
                        'relation_status' => 1, // Active
                        'relation_type'   => 1, // Resident
                    ]);

                    // Send invitation to the Resident
                    if($request->invite_residents) {
                        $activation = new Activation();
                        $activation->createActivation($resident);
                        
                        $activationService = new ActivationService($activation);
                        $activationService->sendActivationMail($resident, false);
                    }
                    $messages .= '<li> Added successfully: ' . $item['Name'] . '</li>';
                } else {
                    $messages .= '<li> Exist: ' . $item['Name'] . '</li>';
                }
            }
            $messages .= '</ul>';
        }

        return response()->json([
            'error' => '',
            'message' => $messages,
            'data' => []
        ], 200);
    }


    /**
     * Update a resident.
     *
     * @param  App\Http\Requests\StoreResidentRequest $request
     * @return Response
     */
    public function updateResident(StoreResidentRequest $request, $user_id) {

        // Validate the Role id
        $role = Role::where('id', $request->role_id)->first(['id', 'name', 'display_name']);

        if( !$role ) {
            return response()->json([
                'error' => __('messages.invalidData', ['type' => 'Resident Level']),
                'message' => '',
                'data' => []
            ], 400);
        }

        // Validate the Building Id
        $building = Building::where('id', $request->building_id)->first(['id']);
        
        if(!$building) {
            return response()->json([
                'error' => __('messages.invalidData', ['type' => 'Building']),
                'message' => '',
                'data' => []
            ], 400);
        }

        // do the user update
        $resident = User::where('id', $user_id)->first();

        $resident->update($request->only($resident->getFillable()));

        /* Building update */

        // resident had no building
        if( $resident->building->isEmpty() ) {

            $resident->buildings()->attach($request->building_id, [
                'unit_no'         => $request->unit_no,
                'unit_type'       => $request->unit_type,
                'relation_start'  => $request->relation_start ? $request->relation_start : NULL, 
                'relation_end'    => $request->relation_end ? $request->relation_end : NULL,
                'relation_status' => 1, // Active
                'relation_type'   => 1, // Resident
                'created_at'      => Carbon::now()
            ]);

        }
        // resident had building
        else {

            // check if the building is changing
            if($request->building_id != $resident->building[0]->id)  {

                // Inactivate the current status
                BuildingUser::where('user_id', $resident->id)->update([
                    'relation_status' => 0,
                    'relation_end' => Carbon::now()
                ]);
    
                // attach the building
                $resident->buildings()->attach($request->building_id, [
                    'unit_no'         => $request->unit_no,
                    'unit_type'       => $request->unit_type,
                    'relation_start'  => $request->relation_start ? $request->relation_start : NULL, 
                    'relation_end'    => $request->relation_end ? $request->relation_end : NULL,
                    'relation_status' => 1, // Active
                    'relation_type'   => 1, // Resident
                    'created_at'      => Carbon::now()
                ]);
            }
            // update the building pivot
            else {
    
                $resident->building[0]->pivot->update([
                    'unit_no'         => $request->unit_no,
                    'unit_type'       => $request->unit_type,
                    'relation_start'  => $request->relation_start ? $request->relation_start : NULL, 
                    'relation_end'    => $request->relation_end ? $request->relation_end : NULL,
                    'relation_status' => 1, // Active
                    'relation_type'   => 1, // Resident
                ]);
            }
        }


        // Response
        return response()->json([
            'error' => '',
            'message' => __('messages.updated', ['type' => $role->display_name]),
            'data' => [
                'id' => $resident->id
            ]
        ], 200);

    }



    /**
     * Send invitation email to a user or resident.
     * 
     * @param int $user_id
     * @return Response
     */
    public function invite(User $user) {

        $activation = new Activation();
        $activation->createActivation($user);
        
        $activationService = new ActivationService($activation);
        $activationService->sendActivationMail($user, false);

        $user->status = User::$STATUS_INVITED;
        $user->save();

        return response()->json([
            'error' => '',
            'message' =>  __('messages.user.invitation_success'),
            'data' => [
                'id' => $user->id
            ]
        ], 200);
    }



    /**
     * Flag/Un-flag a resident in the system
     * 
     * @param Request
     * @param \App\Models\User $user
     * @return Response\Json
     */
    public function flag(Request $request, User $user) {

        if( $user->is_flagged == true ) {

            $user->update([
                'is_flagged' => false,
                'is_flagged_reason' => NULL,
                'status' =>  $user->is_set_password ? User::$STATUS_ACTIVE : User::$STATUS_INACTIVE
            ]);
        } 
        else {

            $user->update([
                'is_flagged' => true,
                'is_flagged_reason' => trim($request->reason),
                'status' =>  User::$STATUS_FLAGGED
            ]);
        }

        return response()->json([
            'error' => '',
            'message' => $user->is_flagged ? __('messages.user.flagged_success') : __('messages.user.unFlagged_success'),
            'data' => [
                'id' => $user->id
            ]
        ], 200);
    }
    


    /**
     * Soft delete the user's account.
     *
     * @param  Illuminate\Http\Request $request
     * @param  int $user_id
     * @return Response
     */
    public function destroy(Request $request, $user_id) {

        // Soft delete
        $user = User::deleteUser($user_id);

        return response()->json([
            'error' => '',
            'message' => __('messages.deleted', ['type' => 'User']),
            'data' => []
        ], 200);
    }



    /**
     * Get the list of Residents
     *
     * @param Request
     * @return Response/Json
     */
    public function getResidents(Request $request) {

        $Residents = User::Residents()->OwnOnly();

        if(!$request->term) {
            $Residents->whereRaw("concat(first_name, ' ', last_name) like '%{$request->term}%' "); 
        }

        $Residents = $Residents->orderByRaw("concat(first_name, ' ', last_name) ASC")
            ->get([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'mobile',
                'is_flagged',
                'status'
            ]);

        return json_encode($Residents);
    }



    /**
     * Get the list of Residents + Admins together
     *
     * @param Request
     * @return Response/Json
     */
    public function getUsers(Request $request) {

        $Users = User::select(DB::raw("                
            users.id,
            concat(first_name, ' ', last_name) as name,
            email,
            role_id,
            mobile,
            is_flagged,
            status,
            roles.display_name as role
        "))
        ->leftJoin('roles', 'roles.id', '=', 'users.role_id');

        // Search term
        if($request->term) {
            $Users->whereRaw("concat(first_name, ' ', last_name) like '%{$request->term}%' "); 
        }

        // Only for a specific building
        if($request->building_id) {

            // residents
            $Users->where('users.role_id', '>=', DB::raw(User::$ROLE_RESIDENT))
                ->where("building_user.building_id", $request->building_id)
                ->leftJoin('building_user', function($join) {
                    $join->on('building_user.user_id', '=', 'users.id');
                    $join->on('building_user.relation_status', '=', DB::raw(true));
                })
                // all the other roles
                ->orWhere('users.role_id', '<', DB::raw(User::$ROLE_RESIDENT));
        }

        $Users = $Users
            ->orderBy("role_id", "DESC")
            ->orderByRaw("concat(first_name, ' ', last_name) ASC")
            ->groupBy('users.id')
            ->get();

        // Group by (Role)
        $data = [];

        foreach($Users->groupBy('role_id') as $key => $items) {

            $data[] = [
                'text' => $items[0]->role.'s',
                'children' => $items->toArray()
            ];
        }

        return json_encode($data);
    }



    /**
     * Get the list of Users. Including [Super Admin | Admin | Building Manager | Staff]
     *
     * @param Request
     * @return Response/Json
     */
    public function getAdmins(Request $request)
    {
        $Users = User::Users();

        if($request->term) {
            $Users->whereRaw("concat(first_name, ' ', last_name) like '%{$request->term}%' "); 
        }

        $Users = $Users->orderByRaw("concat(first_name, ' ', last_name) ASC")
            ->limit(10)
            ->get([
                'id',
                'first_name',
                'last_name',
                'email',
                'phone',
                'mobile',
                'is_flagged',
                'status'
            ]);

        return json_encode($Users);
    }



    /**
     * List Users and return in DataTables.
     *
     * @param  Illuminate\Http\Request $request
     * @return DataTables
     */

    public function ListUsers(Request $request, $tab = null)
    {

        $items = User::select(DB::raw("
            users.id,
            concat(users.first_name, ' ', users.last_name) as name,
            users.email,
            users.mobile,
            users.status,
            users.role_id,
            users.deleted_at,
            roles.display_name as role,

            (SELECT
                GROUP_CONCAT(buildings.name)
                FROM buildings
                LEFT JOIN building_user ON building_user.building_id = buildings.id
                WHERE building_user.user_id = users.id
                AND building_user.relation_status = 1
                AND building_user.relation_type IN (2,3)
            ) AS buildings

        "))
        ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
        ->Users();

        // Archive tab
        if($tab == 'archive') {
            $items = $items->onlyTrashed();
        }

        $JSON = DataTables::of($items)
            // Name
            ->addColumn('name', function(User $user) {
                return '<a href="'.route('app.user.show', $user->id).'" class="row-col title">
                            <span>'.$user->name.'</span>
                            <small data-exclude="true">'.$user->email.'</small>
                        </a>';
            })
            // Mobile
            ->addColumn('mobile', function(User $user) {
                return '<span>'.$user->mobile.'</span>';
            })
            // Role
            ->addColumn('role', function(User $user) {
                return '<span class="row-col">'.$user->role.'</span>';
            })
            // Building
            ->addColumn('building_access', function(User $user) {

                // SuperAdmin|Admin can access to all
                if(in_array($user->role_id, [User::$ROLE_SUPER_ADMIN, User::$ROLE_ADMIN])) {
                    return 'All';
                }
                // Others see own only
                if(!$user->buildings) {
                    return '-';
                }

                $buildings = explode(',', $user->buildings);
                $r = '';
                foreach($buildings as $building) {
                    $r .= '<span class="label">'.$building.'</span>';
                }
                return $r;
            })
            // Status
            ->addColumn('status', function(User $user) {
                return $user->getStatus();
            })
            // Actions
            ->addColumn('actions', function(User $user) {

                $actions = '<div class="btn-hspace">
                            <button type="button" data-toggle="dropdown" class="btn btn-sm btn-i btn-primary btn-simple" aria-expanded="true">
                                Actions <i class="material-icons">expand_more</i>
                            </button>
                            <ul role="menu" class="dropdown-menu dropdown-menu-right">';
                            // Invite user
                            if(in_array($user->status, [User::$STATUS_INACTIVE, User::$STATUS_INVITED])) {
                                $actions .= '<li><a class="actions" data-target="#mod-invite" type="button" href="'.route('app.user.invite', $user->id).'">Invite</a></li>';
                            }
                            // Login as user
                            if( Auth::user()->isSuperAdmin() && ! $user->isSuperAdmin() ) {
                                $actions .= '<li><a href="'.route('impersonate', $user->id).'">Login as</a></li>';
                            }
                            // Delete user
                            if(Auth::user()->canDelete() && (Auth::user()->id !== $user->id)) {
                                $delete_label = $user->trashed() ? 'Delete Permanently' : 'Delete';
                                $actions .= '<li><a class="actions" data-target="#mod-delete" type="button" href="'.route('app.user.delete', $user->id).'">'.$delete_label.'</a></li>';
                            }

                            $actions .= '
                                </ul>
                        </div>';

                return $actions;
            })
            /** 
             * Column Filer
             */
            ->filterColumn('name', function($query, $keyword) {
                $query->whereRaw("concat(users.first_name, ' ', users.last_name) like ?", ["%{$keyword}%"])
                      ->orWhereRaw("users.email like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('mobile', function($query, $keyword) {
                $query->whereRaw("users.mobile like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('role', function($query, $role_id) {
                $query->where("users.role_id", $role_id);
                return;
            })
            ->filterColumn('building_access', function($query, $building_id) {
                $query->whereRaw("? IN (SELECT building_id FROM building_user WHERE building_user.user_id = users.id AND building_user.relation_status = 1 AND building_user.relation_type IN (2,3))", $building_id);
                return;
            })
            ->filterColumn('status', function($query, $status) {
                $query->where("users.status", $status);
                return;
            })
            // Decode HTML chars
            ->rawColumns([
                'name',
                'mobile',
                'role',
                'building_access', 
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
                    case 'role':
                        $query->orderBy("roles.display_name", $dir);
                        break;
                    case 'building_access':
                        $query->orderBy("buildings.name", $dir);
                        break;

                    case 'status':
                        $query->orderBy("users.status", $dir);
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
                    'email'
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
                    return exportToCSV($data_to_export, 'Users Export '.Carbon::now()->format('md_His').'.csv');
                }

            }

            // return for ajax
            return $JSON->toJson();
    }




    /**
     * List Residents and return in DataTables.
     *
     * @param  Illuminate\Http\Request $request
     * @return DataTables
     */

    public function ListResidents(Request $request, $tab = null)
    {

        $items = User::select(DB::raw("
            users.id,
            concat(users.first_name, ' ', users.last_name) as name,
            users.email,
            users.mobile,
            users.status,
            users.is_flagged_reason,
            roles.display_name as role_name,

            buildings.id as building_id,
            buildings.name as building_name,
            buildings.suburb,
            buildings.is_thumb as building_thumb,
            
            building_user.unit_no
        "))
        ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
        ->leftJoin('building_user', function($join) {
            $join->on('building_user.user_id', '=', 'users.id');
            $join->on('building_user.relation_status', '=', DB::raw(true));
            $join->on('building_user.relation_type', '=', DB::raw(BuildingUser::$RELATION_TYPE_RESIDENT));
        })
        ->leftJoin('buildings', 'buildings.id', '=', 'building_user.building_id')
        ->Residents()
        ->OwnOnly()
        ->groupBy('users.id');

        // Archive tab
        if($tab == 'archive') {
            $items = $items->onlyTrashed();
        }

        if($request->building_id) {
            $items = $items->where('buildings.id', $request->building_id); 
        }
        
        $JSON = DataTables::of($items)
            // Name
            ->addColumn('name', function(User $user) {
                return '<a href="'.route('app.resident.show', $user->id).'" class="row-col title">
                            <span>'.$user->name.'</span>
                            <small data-exclude="true">'.$user->email.'</small>
                        </a>';
            })
            // Building
            ->addColumn('building', function(User $user) {
                
                if(!$user->building_name) 
                    return '';

                return '
                    <div class="row-col">
                        '.Building::getThumbOrInitials($user->building_id, $user->building_name, $user->building_thumb).'
                        <span>'.$user->building_name.'</span>
                        <small data-exclude="true">'.$user->suburb.'</small>
                    </div>';
            })
            // Unit No.
            ->addColumn('unit_no', function(User $user) {
                if(!$user->unit_no) 
                    return '';
                return '<b>'.$user->unit_no.'</b>';
            })
            // Resident Level
            ->addColumn('level', function(User $user) {
                return $user->role_name;
            }) 
            // Resident Status
            ->addColumn('status', function(User $user) {
                return $user->getStatus();
            })
            // Actions
            ->addColumn('actions', function(User $user) {
                
                $actions = '<div class="btn-hspace">
                            <button type="button" data-toggle="dropdown" class="btn btn-sm btn-i btn-primary btn-simple" aria-expanded="true">
                                Actions <i class="material-icons">expand_more</i>
                            </button>
                            <ul role="menu" class="dropdown-menu dropdown-menu-right">';
                            // Invite
                            if(in_array($user->status, [User::$STATUS_INACTIVE, User::$STATUS_INVITED])) {
                                $actions .= '<li><a class="actions" data-target="#mod-invite" type="button" href="'.route('app.user.invite', $user->id, false).'">Invite</a></li>';
                            }
                            // Login as resident
                            if( Auth::user()->isSuperAdmin() ) {
                                $actions .= '<li><a href="'.route('impersonate', $user->id).'">Login as</a></li>';
                            }
                            // Delete
                            if( Auth::user()->canDelete() ) {
                                $actions .= '<li><a class="actions" data-target="#mod-delete" type="button" href="'.route('app.user.delete', $user->id, false).'">Delete</a></li>';
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
                $query->where("users.id", $keyword);
                return;
            })
            ->filterColumn('name', function($query, $keyword) {
                $query->whereRaw("concat(users.first_name, ' ', users.last_name) like ?", ["%{$keyword}%"])
                      ->orWhereRaw("users.email like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('mobile', function($query, $keyword) {
                $query->whereRaw("users.mobile like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('building', function($query, $building_id) {
                $query->where("buildings.id", $building_id);
                return;
            })
            ->filterColumn('unit_no', function($query, $keyword) {
                $query->whereRaw("building_user.unit_no like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('level', function($query, $keyword) {
                $query->where("roles.id", $keyword);
                return;
            })
            ->filterColumn('status', function($query, $status) {
                $query->where("users.status", $status);
                return;
            })
            // Decode HTML chars
            ->rawColumns([
                'name', 
                'mobile', 
                'building', 
                'unit_no', 
                'level', 
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
                    case 'id':
                        $query->orderBy('users.id', $dir);
                        break;
                    case 'name':
                        $query->orderBy("name", $dir);
                        break;
                    case 'building':
                        $query->orderBy("buildings.name", $dir);
                        break;
                    case 'unit_no':
                        $query->orderBy("building_user.unit_no", $dir);
                        break;
                    case 'level':
                        $query->orderBy("roles.display_name", $dir);
                        break;
                    case 'status':
                        $query->orderBy("users.status", $dir);
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
                    'email'
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
                    return exportToCSV($data_to_export, 'Residents Export '.Carbon::now()->format('md_His').'.csv');
                }
            }

            // return for ajax
            return $JSON->toJson();
           
    }


    /**
     * Store the resident contact details
     * 
     * @param $request
     * @return $response
     * 
     */
    public function storeUserSetting(Request $request, $user_id) {
        // do user update
        $user = UserSetting::where('user_id', $user_id)->first();

        $update_data = [];
        $update_data['additional_password_prompt'] = !$request->additional_password_prompt ? UserSetting::$ADDITIONAL_PASSWORD_PROMPT_OFF : UserSetting::$ADDITIONAL_PASSWORD_PROMPT_ON;
        
        $user->update($update_data);
        return response()->json([
            'error' => '',
            'message' => 'Saved',
            'data' => []
        ], 200);
    }

}
