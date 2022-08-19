<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseClasses\Controller;

use App\Http\Requests\StoreUserRequest; // todo

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Models\Building;

use App\Models\Booking;
use App\Models\BookableItem;
use App\Models\BookableItemFee;
use App\Models\BookableItem\BookableItemEvent;
use App\Models\BookableItem\BookableItemHire;
use App\Models\BookableItem\BookableItemRoom;
use App\Models\BookableItem\BookableItemService;

use App\Models\User;

use DB;
use Auth;

use Carbon\Carbon;


class CalendarController extends Controller
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
     * Get Events
     *
     * @return Response
     */
    public function get(Request $request) {

        $request = json_decode(json_encode($request->all()));

        $events = $this->getBookings($request);

        //
        return response()->json([
            'error' => null,
            'events' => $events
        ], 200);

    }



    /**
     * Get the Room bookings
     */
    public function getBookings($request) {
        $user = Auth::user();

        $building_hours = "";

        // $bookings = Booking::select(
        //         'bookings.id', 
        //         'bookings.start', 
        //         'bookings.end', 
        //         'bookings.other_fee', 
        //         'bookings.cleaning_required', 
        //         'bookable_items.title',
        //         'bookable_items.type as bookable_items_type',
        //         'bookable_items.is_thumb',
        //         'categories.id as category_id',
        //         'categories.name as category_name',
        //         'categories.color as category_color'
        //     )
        //     ->leftJoin('bookable_items', 'bookable_items.id', '=', 'bookings.bookable_item_id')
        //     ->leftJoin('categories', 'categories.id', '=', 'bookable_items.category_id')
            
        //     ->whereDate('bookings.start', '>=', Carbon::parse($request->start))
        //     ->whereDate('bookings.end', '<=', Carbon::parse($request->end))
        //     ->whereIn('bookings.status', [1,2]); // Active or Confirmed only

        $bookings = Booking::with('user')
            ->with('bookableItem')
            ->with('bookableItem.category:id,name,color')
            ->whereDate('start', '>=', Carbon::parse($request->start))
            ->whereDate('end', '<=', Carbon::parse($request->end))
            // ->whereIn('status', [1,2]); // Active or Confirmed only
            ->whereIn('status', [Booking::$STATUS_ACTIVE, Booking::$STATUS_CONFIRMED, Booking::$STATUS_COMPLETE]); // Active or Confirmed only
        
        // building filter
        if($request->building != 'all') {
     
            $bookings = $bookings->where('bookings.building_id', $request->building);
            
            // Grab the opening hours
            $building_hours = Building::where('id', $request->building)->first('office_hours');
            $building_hours = $building_hours ? $building_hours->office_hours : null;
        }

        // Category filter
        if($request->category != 'all') { 
            $category_ids = explode(',', $request->category);
            $bookings = $bookings->whereIn('bookable_items.category_id', $category_ids);
        }

        // Item filter
        if($request->item != 'all') {
            $item_ids = explode(',', $request->item);
            $bookings = $bookings->whereIn('bookings.bookable_item_id', $item_ids);
        }

        if($user->isExternal()){
            $bookings = $bookings->whereHas('bookableItem', function($q) use($user){
                $q->whereHas('service', function ($q1) use($user) {
                    return $q1->where('assign_to_user_id', $user->id);
                });
             });
        }
        
        $items = $bookings
            ->myBooking()
            ->get();

        // Add some additional parameters
        if($items) {
            foreach ($items as $item) {
                $item->id         = $item->id;
                $item->number     = $item->getNumber();

                $item_title = $item->bookableItem->title;
                $item->title      = $item_title;

                // check other fee
                if($item->bookableItem->type === BookableItem::$TYPE_ROOM) {
                    $item->title = $item_title . ' <span class="text-primary" title="Cleaning required">(C)</span>';
                    if ($item->other_fee && count($item->other_fee) > 0) {
                        foreach ($item->other_fee as $fee) {
                            if ((float) $fee['fee'] == 0) {
                                $item->title = $item_title . ' <span class="text-danger" title="No cleaning required">(NC)</span>';
                            }
                        }
                    }
                    if ($item->isAdminCleaningRequired()) {
                        $item->title = $item_title . ' <span class="text-primary" title="Cleaning required">(C)</span>';
                    }
                    if ($item->isAdminNoCleaningRequired()) {
                        $item->title = $item_title . ' <span class="text-danger" title="No cleaning required">(NC)</span>';
                    }
                }

                $item->groupId    = $item->category_id;
                $item->category   = $item->category_name;
                $item->backgroundColor  = luminance($item->bookableItem->category->color.'90', .75);
                // $item->backgroundColor  = luminance($item->category_color.'90', .75);
                $item->borderColor  = $item->category_color;
            }
        }

        return [
            'events' => $items,
            'building' => json_decode($building_hours)
        ];
    }





}
