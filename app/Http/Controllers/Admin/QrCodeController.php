<?php

namespace App\Http\Controllers\Admin;

use SimpleSoftwareIO\QrCode\Facades\QrCode as SimpleQrCode;

use App\Http\Controllers\BaseClasses\Controller;

use App\Http\Requests\StoreQrCodeRequest;

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

use App\Models\Activation;
use App\Services\ActivationService;

use App\Models\User;
use App\Models\Role;
use App\Models\QrCode;

use DataTables;

use DB;
use Auth;

use Carbon\Carbon;
use Illuminate\Support\Arr;


class QrCodeController extends Controller
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
     * Display the QR Code List
     *
     * @return Response
     */
    public function index($tab = null)
    {
        return view(Route::currentRouteName(), compact('tab'));
    }



    /**
     * QR Code single page view
     *
     * @return Response
     */
    public function show($tab = null)
    {
        return view(Route::currentRouteName(), compact('tab'));
    }


    /**
     * Create a new QR Code (admin).
     *
     * @param  App\Http\Requests\StoreQrCodeRequest $request
     * @return Response
     */
    public function create(StoreQrCodeRequest $request)
    {

        // No permission
        if (!Auth::user()->isSuperAdmin()) {
            return response()->json([
                'error' => 'You have no permission to create new QR Code',
                'data' => [],
            ], 400);
        }


        // Create the new QR Code
        $form_data = $request->all();
        $form_data['type'] = QrCode::$STATUS_ACTIVE;
        $qr_code = QrCode::create($form_data);

        return response()->json([
            'error' => '',
            'message' => $qr_code->name . ' added successfully.',
            'data' => [
                'id' => $qr_code->id
            ]
        ], 200);
    }



    /**
     * Update a user (admin)
     *
     * @param  App\Http\Requests\StoreQrCodeRequest $request
     * @return Response
     */
    public function update(StoreQrCodeRequest $request, $id)
    {

        // No permission
        if (!Auth::user()->isSuperAdmin()) {
            return response()->json([
                'error' => 'You have no permission to create new users',
                'data' => [],
            ], 400);
        }

        // do user update
        $qr_code = QrCode::find($id);

        $qr_code->update($request->only($qr_code->getFillable()));

        return response()->json([
            'error' => '',
            'message' => 'Updated successfully.',
            'data' => [
                'id' => $qr_code->id
            ]
        ], 200);
    }


    /**
     * Get detail api
     *
     * @param  App\Http\Requests\StoreQrCodeRequest $request
     * @return Response
     */
    public function getDetail(Request $request, $id)
    {

        // No permission
        if (!Auth::user()->isSuperAdmin()) {
            return response()->json([
                'error' => 'You have no permission to create new users',
                'data' => [],
            ], 400);
        }

        // do user update
        $qr_code = QrCode::find($id);
        if($qr_code) {
            $qr_code->qr_code = 'data:image/png;base64, '.base64_encode(SimpleQrCode::format('png')->size(150)->generate($qr_code->content));
        }


        return response()->json([
            'error' => '',
            'message' => '',
            'data' => $qr_code
        ], 200);
    }

    /**
     * List Qr Code and return in DataTables.
     *
     * @param  Illuminate\Http\Request $request
     * @return DataTables
     */

    public function listUsers(Request $request, $tab = null)
    {
        $items = QrCode::select(['id', 'name', 'content', 'type', 'created_at', 'updated_at']);

        // Archive tab
        if ($tab == 'archive') {
            $items = $items->onlyTrashed();
        }

        $JSON = DataTables::of($items)
            // Name
            ->addColumn('name', function (QrCode $item) {
                return '<a href="' . route('app.qr-code.show', $item->id) . '" class="row-col title">
                            <span>' . $item->name . '</span>
                            <small data-exclude="true">' . $item->email . '</small>
                        </a>';
            })
            // Content
            ->addColumn('content', function (QrCode $item) {
                return '<span style="white-space: initial;">' . $item->content . '</span>';
            })
            // QR Code
            ->addColumn('qrcode', function (QrCode $item) {
                return '<div class="text-center"><img src="data:image/png;base64, '.base64_encode(SimpleQrCode::format('png')->size(150)->generate($item->content)) .'"></div>';
            })
            // Status
            ->addColumn('type', function (QrCode $item) {
                return $item->getStatus();
            })
            // Actions
            ->addColumn('actions', function (QrCode $item) {

                $actions = '<div class="btn-hspace">
                            <button type="button" data-toggle="dropdown" class="btn btn-sm btn-i btn-primary btn-simple" aria-expanded="true">
                                Actions <i class="material-icons">expand_more</i>
                            </button>
                            <ul role="menu" class="dropdown-menu dropdown-menu-right">';
                if (Auth::user()->canDelete()) {
                    $actions .= '<li><a class="actions" data-target="#mod-cancel" type="button" href="' . route('app.qr-code.delete', $item->id) . '">Delete</a></li>';
                    $actions .= '<li><a class="actions print_qr_code" type="button" href="#" data-id="'.$item->id.'">Print</a></li>';
                }
                $actions .= '
                            </ul>
                        </div>';

                return $actions;
            })
            /** 
             * Column Filer
             */
            ->filterColumn('name', function ($query, $keyword) {
                $query->whereRaw("name like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('content', function ($query, $keyword) {
                $query->whereRaw("content like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('type', function ($query, $status) {
                $query->where("type", $status);
                return;
            })
            // Decode HTML chars
            ->rawColumns([
                'name',
                'content',
                'qrcode',
                'type',
                'status',
                'actions'
            ])
            // Column Order
            ->order(function ($query) {
                $dir = 'asc';
                $order = request()->input('order');
                $order_by = null;
                if ($order) {
                    $col_inx = $order[0]['column'];
                    $order_by = request()->input('columns')[$col_inx]['data'];
                    $dir = $order[0]['dir'];
                }
                switch ($order_by) {
                    case 'name':
                        $query->orderBy("name", $dir);
                        break;
                    case 'content':
                        $query->orderBy("content", $dir);
                        break;
                    case 'type':
                        $query->orderBy("type", $dir);
                        break;
                }
            });

        // return for ajax
        return $JSON->toJson();
    }

    /**
     * Soft delete (Cancel) a booking
     *
     * @param  Illuminate\Http\Request $request
     * @param  int $booking_id
     * @return Response/Json
     */
    public function destroy(Request $request, $id)
    {
        $qr_code = QrCode::find($id);

        if(!$qr_code) {
            // can not found building
            return response()->json([
                'error' => 'QR Code not found, or you have no permission to proceed with this action',
                'data' => [],
            ], 400);
        }

        // init the cancel flow
        $qr_code->delete();

        //
        return response()->json([
            'error' => '',
            'data' => compact(['qr_code']),
            'message' => 'Deleted.',
        ], 200);
    }
}
