<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Models\RetailDeal;

use DataTables;
use Auth;
use Carbon\Carbon;

class DealsController extends Controller
{
    
    /**
     * Instantiate a new controller instance
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
    }


    /**
     * Display the Deal List
     *
     * @return Response
     */
    public function index() {  
        return view(Route::currentRouteName());
    }

    /**
     * Display the Redeemed deals
     *
     * @return Response
     */
    public function history() {
        return view(Route::currentRouteName());
    }


    /**
     * Show the deal single page
     *
     * @return Response
     */
    public function show() {
        return view(Route::currentRouteName());
    }



    /**
     * Redeem a deal by deal id.
     * @return Response/Json
     */
    public function redeem(Request $request) {

    
        $deal = RetailDeal::where('id', $request->deal_id)
                    ->where('status', RetailDeal::$STATUS_ACTIVE)
                    ->first(['id', 'allowed_redeem_num']);

        if(!$deal) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Deal']),
                'data' => []
            ], 400);
        }

        // Check whether the user can redeem this deal
        $redeem_num = Auth::user()->redeemedDeals->where('id', $deal->id)->count();

        // User cannot redeem this item any longer
        if($deal->allowed_redeem_num && ($redeem_num >= $deal->allowed_redeem_num) ) {
            return response()->json([
                'error' => __('messages.deals.redeem_limit_reached'),
                'data' => []
            ], 400);
        }

        $code = str_random(6);

        Auth::user()->redeemedDeals()->attach($deal, ['code' => $code]);

        // add the current redeem to the counter
        $current_redeem_num = $redeem_num + 1;
        $remaining_redeem_num = $deal->allowed_redeem_num - $current_redeem_num;
        
        return response()->json([
            'error' => '',
            'message' => __('messages.deals.redeem_success'),
            'data' => [
                'deal_id' => $deal->id,
                'code' => $code,
                'remaining_redeem' => $deal->allowed_redeem_num ? $remaining_redeem_num : false,
                'label' => $deal->allowed_redeem_num ? "Redeem {$remaining_redeem_num} more ".str_plural('time', $remaining_redeem_num) : false,
            ]
        ], 200);
    }


    /**
     * List Redeem history and return in DataTables.
     *
     * @param  Illuminate\Http\Request $request
     * @return DataTables
     */

    public function List(Request $request)
    {

        $items = Auth::user()->redeemedDeals()
                    ->select([
                        'retail_deals.id', 
                        'store_id', 
                        'title', 
                        'subtitle', 
                        'retail_deals.thumb', 
                        'code', 
                        'user_deal_redeems.created_at',
                        'retail_stores.name as store_name'
                    ])
                    ->leftJoin('retail_stores', 'retail_stores.id', '=', 'retail_deals.store_id')
                    ->withTrashed();

        return DataTables::of($items)
            // Code
            ->addColumn('code', function(RetailDeal $deal) {
                return '<span class="_id">'.$deal->code.'</span>';
            })
            //
            ->addColumn('deal', function(RetailDeal $deal) {
                $a = '<span class="row-col title">';
                if($deal->thumb) {
                    $a .= '<span class="initials _bg" style="background-image: url('.$deal->getThumb('180x180').')"></span>';
                }
                $a .= '<b>'.$deal->title.'</b>';
                if($deal->subtitle) {
                    $a .= '<small>'.$deal->subtitle.'</small>';
                }
                $a .= '</span>';
                return $a;
            })
            //
            ->addColumn('store', function(RetailDeal $deal) {
                return $deal->store_name;
            })
            // Date
            ->addColumn('date', function(RetailDeal $deal) {
                return '
                <span class="date">'.dateFormat($deal->created_at).'</span>
                <span class="time">'.timeFormat($deal->created_at).'</span>';
            })

            // Decode HTML chars
            ->rawColumns(['code', 'deal', 'store', 'date'])

            // Order
            ->order(function ($query) {
                $query->orderBy('created_at', 'DESC');
            })
            ->make(true);
    }


}
