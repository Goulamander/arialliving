<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests\StoreBookableItemRequest;

// use App\Events\LocationUpdated;

use App\Models\User;
use App\Models\Building;

use App\Models\BookableItem;
use App\Models\BookableItem\BookableItemEvent;
use App\Models\BookableItem\BookableItemHire;
use App\Models\BookableItem\BookableItemRoom;
use App\Models\BookableItem\BookableItemService;

use App\Models\RecurringEvent;

use App\Models\Comment;

use DataTables;
use DB;
use Auth;
use Hash;

use Carbon\Carbon;

class BookableItemController extends Controller
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
     * Display the Bookable Item List
     *
     * @return Response
     */
    public function index() {  
        return view(Route::currentRouteName());
    }



    /**
     * Bookable Item single page view
     *
     * @return Response
     */
    public function show() {
        return view(Route::currentRouteName());
    }


    /**
     * Get a Bookable Item
     *
     * @param  int $item_id
     * @param  bool $with_relations
     * @return Response
     */

    public function get($item_id, $with_relations = true)
    {
        if(!$item_id) {
            abort('404');
        }

        if(!$with_relations) {
            $BookableItem = BookableItem::where('id', $item_id)->findOrFail();
        }
        else {
            $BookableItem = BookableItem::where('id', $item_id)
                ->with('building')
                ->with('comments')
                // ->with('user.profile:user_id,phone_country_code,phone,mobile_country_code,mobile') 
                ->withTrashed()
                ->firstOrFail();
        }

        $data = compact(
            'BookableItem'
        );

        $data['data_id'] = $item_id;

        return response()->json([
            'error' => '',
            'data' => $data
        ], 200);

    }

    
    /**
     * Get the Cancellation Cut Off
     * 
     * @param Request $request
     * @param int $type
     * @param int $item_id
     * 
     * @return Carbon $date
     */
    public function getCutOffDate(Request $request, $type, $item_id) {

        if(!$request->booking_start) {
            return response()->json([
                'error' => 'No booking start date provided',
                'data' => []
            ], 400);
        }

        $BookableItem = BookableItem::where('id', $item_id)->first();
        
        $data = [
            'cut_off_date' => $BookableItem->getCutOffDate($request->booking_start)
        ];

        return response()->json([
            'error' => '',
            'data' => $data
        ], 200);
    }


    /**
     * Validate times for date
     */
    public function validateDate(Request $request, $type, $item_id) {

        $BookableItem = BookableItem::where('id', $item_id)->first();

        if(!$BookableItem) {
            return response()->json([
                'error' => 'Cannot find bookable item.',
                'data' => null
            ], 400);
        }
        
        $data = $BookableItem->getAvailableTimes($request->all());

        return response()->json([
            'error' => '',
            'data' => $data
        ], 200);
    }


    /**
     * Get the date parameters in a provided date-range
     * 
     * @param request 
     * @param str $type - item_type
     * @param int $item_id
     * 
     * @return Response
     */
    public function getDatesInPeriod(Request $request, $type, $item_id) {

        if( ! $request->start || ! $request->end ) {
            return response()->json([
                'error' => 'Missing date parameter',
                'data' => null
            ], 400);
        }

        $item = BookableItem::where('id', $item_id)
            ->with('hire')
            ->with('room')
            ->with('service')
            ->with('building')
            ->first();

        $item->NAME_OF_DATE = BookableItemService::$NAME_OF_DATE;
        
        if( !$item ) {
            return response()->json([
                'error' => 'Cannot find bookable item.',
                'data' => null
            ], 400);
        }
        
        $dates = $item->getUnavailableDates($request->start, $request->end, $request->exclude_booking_id);

        $data = compact(['item', 'dates']);

        return response()->json([
            'error' => '',
            'data' => $data
        ], 200);
    }

    /**
     * Confirm password when price over limit
     * 
     * @param request 
     * @param str $type - item_type
     * @param int $item_id
     * 
     * @return Response
     */
    public function confirmPassword(Request $request, $type, $item_id) {
        if(isset($request->password)) {
            $user = Auth::user();
            if (Hash::check($request->password, $user->password)) {
                return response()->json([
                    'error' => '',
                    'data' => []
                ], 200);
            }
    
            return response()->json([
                'error' => 'Invalid password',
                'data' => ''
            ], 400);
        }
        return response()->json([
            'error' => 'Error',
            'data' => ''
        ], 400);
    }






    /**
     * Detect schedule change
     *  
     * @param App\Models\BookableItem\BookableItemEvent (before the update)
     * @param App\Models\BookableItem\BookableItemEvent (current)
     */
    private function _EventScheduleChangeNotification($original_event, $event) {
        // todo: check for date changes for both single or recurring
    }


    /**
     * Send Event Invitation Emails
     * 
     * @param App\Models\BookableItem
     * @param App\Models\BookableItem\BookableItemEvent
     */
    private function _SendEventInvitationEmails($Item, $Event) {
        // todo
    }


    /**
     * Send Event Canceled Notifications to all Participants, or invitees
     * 
     * @param App\Models\BookableItem
     * @param App\Models\BookableItem\BookableItemEvent
     */
    private function _SendEventCancelNotifications($Item, $Event) {
        // todo
    }



}
