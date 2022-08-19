<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Arr;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests\StoreBookableItemRequest;
use App\Http\Requests\StoreLineItemRequest;

use App\Models\User;
use App\Models\Building;

use App\Models\BookableItem;
use App\Models\BookableItemFee;
use App\Models\BookableItem\BookableItemEvent;
use App\Models\BookableItem\BookableItemHire;
use App\Models\BookableItem\BookableItemRoom;
use App\Models\BookableItem\BookableItemService;
use App\Models\LineItem;
use App\Models\RecurringEvent;

use App\Models\Comment;

use DataTables;
use DB;
use Auth;
use Storage;

use Carbon\Carbon;
use App\Traits\FileManager;


class BookableItemController extends Controller
{
    
    use FileManager;

    /**
     * Instantiate a new controller instance
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth');
        $this->middleware('role:super-admin|building-manager|admin|external');
    }



    /**
     * Display the Bookable Item List
     *
     * @return Response
     */
    public function index($tab = null) {
        return view(Route::currentRouteName(), compact('tab'));
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
            return response()->json([
                'error' => __('messages.notFound', ['Bookable Item']),
                'data' => []
            ], 400);
        }

        if(!$with_relations) {
            $BookableItem = BookableItem::where('id', $item_id)->findOrFail();
        }
        else {
            $BookableItem = BookableItem::where('id', $item_id)
                ->with('building')
                ->with('comments')
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
     * Get the list of Bookable items
     * 
     * @param Request
     * @return Json
     */
    public function getBookableItems(Request $request) {

        $BookableItems = BookableItem::select(DB::raw("
            bookable_items.id,
            category_id,
            title,
            categories.name as category_name,
            is_thumb,
            `bookable_items`.`order`
        "))
        ->leftJoin('categories', 'categories.id', '=', 'bookable_items.category_id')
        ->where('bookable_items.status', 1);

        // Search term
        if($request->term) {
            $BookableItems = $BookableItems->whereRaw("bookable_items.title like '%{$request->term}%'");
        }

        // Only for a specific building
        if($request->building_id && $request->building_id != 'all') {
            $BookableItems = $BookableItems->where("bookable_items.building_id", $request->building_id);
        }

        // Only for a specific category
        // if($request->category_id) {
        //     $BookableItems = $BookableItems->where("bookable_items.building_id", $request->building_id);
        // }

        $BookableItems = $BookableItems
            ->orderBy('categories.order', 'ASC')
            ->orderBy('bookable_items.order', 'ASC')
            ->myItems()
            ->get();

        // Group by (Category)
        $data = [];

        foreach($BookableItems->groupBy('category_id') as $key => $items) 
        {
            $data[] = [
                'text' => $items[0]->category_name,
                'children' => $items->toArray()
            ];
        }

        return json_encode($data);
    }



    /**
     * Create or Update a Bookable Item
     *
     * @param  Illuminate\Http\StoreBookableItemRequest $request
     * @param  str $type
     * @param  int $item_id || 0
     * 
     * @return Response
     */

    public function store(StoreBookableItemRequest $request, $type, $item_id = 0) {

        $is_new = $item_id == 0 ? true : false;

        $data = $request->all();

        // Office Hours: Process the office hours -> collect and create array      
        if(isset($request->set_custom_hours) && $request->set_custom_hours == true) {
            $office_hours = _jsonOfficeHours($request->all());
            $data['office_hours'] = $office_hours;
        }

        if($item_id && isset($request->set_custom_hours) && $request->set_custom_hours == false) {
            $data['office_hours'] = NULL;
        }

        // service timeslot selection
        if(isset($request->is_date) && ($request->is_date == BookableItemService::$IS_DATE_ADD_DATE_AND_TIME_RESTRICTED || $request->is_date == BookableItemService::$IS_DATE_ADD_TIMESLOT || $request->is_date == BookableItemService::$IS_DATE_ADD_DATE_RESTRICTED)) {
            $office_hours = _jsonOfficeHours($request->all());
            $data['office_hours'] = $office_hours;
        }
        // get the type_id
        $type_id = BookableItem::getTypeID($type);

        $data['is_free'] = $this->_isFree($request, $type_id);
        $data['price_tag'] = $this->_generatePriceTag($request, $type_id);

        // Add values for new items only
        if( $is_new ) {
            $data['type'] = $type_id;
            $data['created_by'] = Auth::id();
        }

        // Create or update the bookable item
        $Item = BookableItem::withTrashed()->updateOrCreate(['id' => $item_id], $data);

        
        // delete old bookable fee
        if(isset($item_id) && $item_id > 0) {
            BookableItemFee::where('bookable_item_id', $item_id)->delete();
        }
        if(isset($data['clearing_fee'])) {
            // Create or update the bookable fee
            $clearing_fees = $data['clearing_fee'];
            foreach($clearing_fees as $key => $clearing_fee) {
                $clearing_fees[$key]['bookable_item_id'] = $Item->id;
            }
            $ItemFee = BookableItemFee::insert($clearing_fees);
        }

        // No item found or could been created.
        if(!$Item) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Item']),
                'data' => []
            ], 400);
        }

        // Store the item type specific data
        switch($type) {

            case 'event':

                $event_data = (object) $request->all();

                // 
                if(!$event_data->attendees_limit) {
                    $event_data->attendees_limit = NULL;
                }

                // Repeating event
                if($event_data->event_type == BookableItemEvent::$TYPE_REPEATING) 
                {
                    $event_data->event_from = $event_data->recurring_event_from;
                    $event_data->event_to   = $event_data->recurring_event_to;
                    
                    if($event_data->recurring_all_day == true) {
                        $event_data->all_day = true;
                        $event_data->event_from = NULL;
                        $event_data->event_to = NULL;
                    }
                    else {
                        $event_data->all_day = false;
                    }
                }

                // Single Full day event
                if( $event_data->event_type == BookableItemEvent::$TYPE_SINGLE && $event_data->all_day == true ) 
                {
                    $event_data->all_day = true;
                    $event_data->event_from = NULL;
                    $event_data->event_to = NULL;
                }


                // Store/Update
                $Event = $Item->event()->updateOrCreate([], (array) $event_data);

                // Sync the Recurring Options
                $RecurringEvent = new RecurringEvent();
                $RecurringEvent->SaveOrDrop($request->all(), $Item);
                
                    // Send Notification: Schedule Change
                    // if(!$is_new && ($Item->event->isDirty('event_date') || $Item->event->isDirty('event_from') || $Item->event->isDirty('event_to') ) ) {
                    //     $this->_EventScheduleChangeNotification($Original_event, $Event);
                    // }

                    // // Send Notification: Event Cancel
                    // if( !$is_new && $Item->isDirty('status') && $Item->status == BookableItem::$STATUS_CANCELLED ) {
                    //     $this->SendEventCancelNotifications($Item, $Event);
                    // }
                break;

            case 'room':
                $Room = $Item->room()->updateOrCreate(['bookable_item_id' => $Item->id], $request->all());
                break;

            case 'hire':
                $Hire = $Item->hire()->updateOrCreate([], $request->all());
                break;

            case 'service':
                $service_data = $request->all();
                // service session length change hours:minutes to float
                if(isset($service_data['session_length'])) {
                    $startTime = Carbon::parse('00:00:00');
                    $finishTime = Carbon::parse($service_data['session_length']);
                    $diffInMinutes = $finishTime->diff($startTime)->format('%H.%I');
                    $service_data['session_length'] = $diffInMinutes;
                }
                $Service = $Item->service()->updateOrCreate(['bookable_item_id' => $Item->id], $service_data);
                break;
        }

        return response()->json([
            'error' => '',
            'data' => ['id' => $Item->id]
        ], 200);

    }


    /**
     * Clone a bookable item
     */
    public function clone($item_id) {

        $item = BookableItem::withTrashed()->where('id', $item_id)
            ->with('room')
            ->with('hire')
            ->with('event')
            ->with('service')
            ->with('recurring')
            ->with('line_items')
            ->first();

        if(!$item) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Item']),
                'data' => [],
            ], 400);
        }

        $newItem = $item->replicate();

        $newItem->title = $item->title.' - Copy';
        $newItem->status = BookableItem::$STATUS_DRAFT;
        $newItem->deleted_at = NULL;
        $newItem->is_thumb = NULL;
        $newItem->save();

        switch($newItem->type) {

            case BookableItem::$TYPE_ROOM:
                $newItemRoom = $item->room->replicate();
                $newItemRoom->bookable_item_id = $newItem->id;
                $newItemRoom->save();
                break;

            case BookableItem::$TYPE_HIRE:
                $newItemHire = $item->hire->replicate();
                $newItemHire->bookable_item_id = $newItem->id;
                $newItemHire->save();
                break;

            case BookableItem::$TYPE_EVENT:
                $newItemEvent = $item->event->replicate();
                $newItemEvent->bookable_item_id = $newItem->id;
                $newItemEvent->save();
                // recurring settings
                if($item->recurring) {
                    $newItemRecurring = $item->recurring->replicate();
                    unset($newItemRecurring->id);
                    $newItemRecurring->bookable_item_id = $newItem->id;
                    $newItemRecurring->save();
                }
                break;

            case BookableItem::$TYPE_SERVICE:
                $newItemService = $item->service->replicate();
                unset($newItemService->id);
                $newItemService->bookable_item_id = $newItem->id;
                $newItemService->save();
                // line items
                if($item->line_items) {
                    foreach($item->line_items as $line_item) {
                        $L = $line_item->replicate();
                        $L->item_id = $newItem->id;
                        $L->save();
                    }
                }
                break;
        }

        $newItem->save();

        return response()->json([
            'error' => '',
            'message' => __('messages.cloned', ['type' => 'Bookable item']),
            'data' => ['id' => $newItem->id]
        ], 200);

    }



    /**
     * Publish an item    
     *  
     * @param Request $request
     * @param int $item_id
     */
    public function publish(Request $request, $item_id) {

        $item = BookableItem::where('id', $item_id)->update(['status' => BookableItem::$STATUS_ACTIVE]);

        if($item) {
            return response()->json([
                'error' => '',
                'data' => ['id' => $item_id], 
            ], 200);
        }

        return response()->json([
            'error' => __('messages.notFound', ['type' => 'Item']),
            'data' => [],
        ], 400);
    }



    /**
     * Store the content of a bookable item
     * 
     */
    public function storeSingle(Request $request, $item_id) {

        $itemModel = new BookableItem();

        $item = BookableItem::where('id', $item_id)
            ->withTrashed()
            ->update($request->only($itemModel->getFillable()));

        return response()->json([
            'error' => '',
            'data' => ['id' => $item_id]
        ], 200);

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



    /**
     * Soft delete a Bookable Item
     *
     * @param  Illuminate\Http\Request $request
     * @param  int $item_id
     * @return Response
     */
    public function destroy(Request $request, $item_id)
    {

        if( ! Auth::user()->isSuperAdmin() ) {
            return response()->json([
                'error' => __('messages.noPermission'),
                'data' => []
            ], 400);
        }

        $item = BookableItem::where('id', $item_id)
            ->withTrashed()
            ->first();

        if(!$item) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Item']),
                'data' => []
            ], 400);
        }

        // Permanent delete
        if( $item->trashed() ) {
            $item->forceDelete();
        }

        $item->delete();

        return response()->json([
            'error' => '',
            'message' => __('messages.deleted', ['type' => 'Item']),
            'data' => []
        ], 200);
    }




    /** Service: Line Items */


    /**
     * Get a line item by id
     * @param int $line_item_id
     * @return Response/Json
     */
    public function getLineItem($line_item_id) 
    {
        // todo: add user validation !!!
   
        $lineItem = LineItem::where('id', $line_item_id)->first();

        if(!$lineItem) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Service item']),
                'data' => []
            ], 400);
        }

        $lineItem->update_route = route('app.item.lineItems.store', ['item_id' => $lineItem->item_id, 'line_item_id' => $lineItem->id]);
        $lineItem->thumb_path = $lineItem->getThumb('820x500');

        return response()->json([
            'error' => '',
            'data' => $lineItem
        ], 200);
    }



    /**
     * Store a Line item
     * 
     * @param  App\Http\Requests\StoreLineItemRequest $request
     * @param int $item_id - bookable_item_id
     * @param int $line_item_id || 0
     * @return Response/Json
     */
    public function storeLineItem(StoreLineItemRequest $request, $item_id, $line_item_id = 0) 
    {

        $is_new = $line_item_id == 0 ? true : false;
        
        $bookable_item = BookableItem::where('id', $item_id)
            ->myItems()
            ->first();

        if( !$bookable_item ) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Service']),
                'data' => []
            ], 400);
        }

        // Store
        $lineItem = $bookable_item->line_items()->updateOrCreate(['id' => $line_item_id], $request->all());

        // Store the thumbnail
        if($request->thumb) {

            $thumb_name = $this->uploadInlineThumbnail($request->thumb, $lineItem->imagePath());

            if($thumb_name) {
                $lineItem->thumb = $thumb_name;
                $lineItem->save();
            }
        }

        // On deal update: Check if the thumb has been removed
        if($is_new == false && (!$request->thumb && !$request->filepond)) {

            $is_removed = $this->removeInlineThumbnail($lineItem->imagePath(), $lineItem->thumb);

            if($is_removed) {
                $lineItem->thumb = NULL;
                $lineItem->save();
            }
        }

        if( !$lineItem ) {
            return response()->json([
                'error' => $line_item_id == 0 ? __('messages.cannotSave', ['type' => 'Service item']) : __('messages.notFound', ['type' => 'Service item']),
                'data' => []
            ], 400);
        }

        return response()->json([
            'error' => '',
            'message' => $line_item_id == 0 ? __('messages.created', ['type' => 'Service item']) : __('messages.updated', ['type' => 'Service item']),
            'data' => [
                'id' => $lineItem->id
            ]
        ], 200);
     
    }



    /**
     * Clone a Line item
     */
    public function cloneLineItem($item_id, $line_item_id) {
        
        $bookable_item = BookableItem::where('id', $item_id)
            ->myItems()
            ->first();

        if( !$bookable_item ) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Service']),
                'data' => []
            ], 400);
        }

        $lineItem = $bookable_item->line_items()
            ->where('id', $line_item_id)
            ->first();

        if( !$lineItem ) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Service item']),
                'data' => []
            ], 400);
        }

        $newlineItem = $lineItem->replicate();

        $newlineItem->name = $lineItem->name.' - Copy';
        $newlineItem->status = LineItem::$STATUS_INACTIVE;
        $newlineItem->thumb = NULL;
        $newlineItem->save();

        return response()->json([
            'error' => '',
            'message' => __('messages.cloned', ['type' => 'Service item']),
            'data' => ['id' => $newlineItem->id]
        ], 200);

        
    }



    /**
     * Delete a line item
     */
    public function deleteLineItem($item_id, $line_item_id) {
      
        $bookable_item = BookableItem::where('id', $item_id)
            ->myItems()
            ->first();

        if( !$bookable_item ) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Service']),
                'data' => []
            ], 400);
        }

        $lineItem = $bookable_item->line_items()
            ->where('id', $line_item_id)
            ->first();

        if( !$lineItem ) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Service item']),
                'data' => []
            ], 400);
        }

        // Soft delete
		$lineItem->deleted_at = Carbon::now()->toDateTimeString();
		$lineItem->status = LineItem::$STATUS_DELETED;
        $lineItem->save();
        
        return response()->json([
            'error' => '',
            'message' => __('messages.deleted', ['type' => 'Service item']),
            'data' => []
        ], 200);
        
    }





    /*************************************************
     ************ Private Helper Methods *************
     *************************************************/

    /**
     * is item Free ?
     * 
     * @param Request $request
     * @param int $type_id
     * @return boolean
     */
    private function _isFree(Request $request, $type_id) {
        
        if( !$type_id ) {
            return false;
        }
        
        switch($type_id) {

            // check for admin fee
            case BookableItem::$TYPE_ROOM:
            case BookableItem::$TYPE_EVENT:
                // return ($request->admin_fee && $request->admin_fee > 0) ? false : true;
                if($request->admin_fee && $request->admin_fee > 0) {
                    return false;
                } else if (isset($request->clearing_fee) && count($request->clearing_fee) > 0) {
                    return false;
                } else {
                    return true;
                }
                break;

            // check for hire price
            case BookableItem::$TYPE_HIRE:
                return ($request->item_price && $request->item_price > 0) ? false : true;
                break;

            case BookableItem::$TYPE_SERVICE:
                return $request->is_free == BookableItemService::$PAYMENT_TO_ARIA_YES;
                break;
        }
    
    }


    /**
     * Generate the Json format price Tag 
     * 
     * @param Request $request
     * @param int $type_id
     * @return json
     */
    private function _generatePriceTag(Request $request, $type_id) {
        
        if( !$type_id ) {
            return '';
        }

        switch($type_id) {

            // Booking Fee
            case BookableItem::$TYPE_ROOM:
                if( !$this->_isFree($request, $type_id) ) {
                    return json_encode([
                        'price' => $request->admin_fee,
                        'tag' => ' booking fee'
                    ]);
                }
                return '';
                break;

            // Price /person
            case BookableItem::$TYPE_EVENT:
                if( !$this->_isFree($request, $type_id) ) {
                    return json_encode([
                        'price' => $request->admin_fee,
                        'tag' => '/person'
                    ]);
                }
                return '';
                break;

            // Item Price
            case BookableItem::$TYPE_HIRE:
                if( !$this->_isFree($request, $type_id) ) {
                    return json_encode([
                        'price' => $request->item_price,
                        'tag' => '/'.$request->item_price_unit
                    ]);
                }
                return '';
                break;

            case BookableItem::$TYPE_SERVICE:
                return '';
                break;
        }

    }












    /**
     * List Bookable Items and return in DataTables.
     *
     * @param  Illuminate\Http\Request $request
     * @return DataTables
     */

    public function List(Request $request, $tab = null)
    {
 
        $items = BookableItem::select(DB::raw("
            bookable_items.id,
            bookable_items.title,
            bookable_items.type,
            bookable_items.is_private,
            bookable_items.category_id,
            bookable_items.status,
            bookable_items.is_thumb,
            bookable_items.order,
            bookable_items.is_free,
            bookable_items.price_tag,
            bookable_items.deleted_at,
            
            -- event details
            IF(bookable_item_event.location_id IS NOT NULL, l.title, bookable_item_event.location_name) as location_name,
            bookable_item_event.event_type,
            bookable_item_event.event_date,
            bookable_item_event.event_from,
            bookable_item_event.event_to,
            
            -- repeating events
            recurring_event.repeat_every,
            recurring_event.frequency,
            recurring_event.repeat_next,
            
            -- hire details
            bookable_item_hire.available_qty,

            -- service details
            bookable_item_service.assign_to_user_id,

            -- category
            categories.name as category,
            
            -- building
            buildings.id as building_id,
            buildings.name as building_name
        "))
        ->leftJoin('categories', 'categories.id', 'bookable_items.category_id')
        ->leftJoin('buildings', 'buildings.id', 'bookable_items.building_id')
        // Events
        ->leftJoin('bookable_item_event', 'bookable_item_event.bookable_item_id', 'bookable_items.id')
        ->leftJoin('bookable_items as l', 'l.id', '=', 'bookable_item_event.location_id')
        ->leftJoin('recurring_event', 'recurring_event.bookable_item_id', 'bookable_items.id')

        // Hire items
        ->leftJoin('bookable_item_hire', 'bookable_item_hire.bookable_item_id', 'bookable_items.id')

        // Service items
        ->leftJoin('bookable_item_service', 'bookable_item_service.bookable_item_id', 'bookable_items.id')
        // validate access
        ->myItems(true)
        // Group it by id
        ->groupBy('bookable_items.id');
       

        // Archive tab
        if($tab == 'archive') {
            $items = $items->onlyTrashed();
        }

        $JSON = DataTables::of($items)
            // id
            ->addColumn('id', function(BookableItem $item) {
                return $item->id;
            })
            // Title
            ->addColumn('title', function(BookableItem $item) {

                $private = $item->is_private ? '<span class="label l-red">Private</span>' : '';

                $a = '<a href="'.route('app.item.show', $item->id).'" class="row-col title">';
                if($item->is_thumb) {
                    $a .= '<span class="initials _bg" style="background-image: url('.$item->getThumb().')"></span>';
                }
                $a .= '
                        <span>'.$item->title.$private.'</span>
                        <small data-exclude="true">'.$item->getTypeLabel().'</small>
                    </a>';
                return $a;
            })
            // Category
            ->addColumn('category', function(BookableItem $item) {
                return $item->category;
            })

            // Building
            ->addColumn('building', function(BookableItem $item) {
                if(!$item->building_id) {
                    return '-';
                }
                return '<span class="label">'.$item->building_name.'</span>';
            })

            // Details
            ->addColumn('details', function(BookableItem $item) {

                switch($item->type) {

                    case BookableItem::$TYPE_ROOM:
                        if ($item->is_free == false && !Auth::user()->isResidentVip()) {
                            return '<ul><li>'.$item->getPriceTag().'</li></ul>';
                        }
                        return '-';
                        break;

                    case BookableItem::$TYPE_HIRE:
                        if ($item->is_free == false && !Auth::user()->isResidentVip()) {
                            $a = '<li>'.$item->getPriceTag().'</li>';
                        }
                        else {
                            $a = '<li>Free item</li>';
                        }
                        // qty
                        $a .= '<li>Available QTY: <b>'.$item->available_qty.'</b></li>';
                        return '<ul>'.$a.'</ul>';
                        break;

                    case BookableItem::$TYPE_EVENT:
                        $a = "<li>".BookableItem::getEventType($item->event_type)." Event @".$item->location_name."</li>";

                        // repeating event
                        if($item->event_type == 2) {
                            $time = $item->event_from ? bookingTime($item->event_from, $item->event_to) : 'All day';
                            $a .= "
                                <li><i class='icon-refresh'></i><b>".bookingDate($item->repeat_next, $item->repeat_next)."</b> - ".$time."</li>";
                        }
                        // single event
                        else {
                            $time = $item->event_from ? bookingTime($item->event_from, $item->event_to) : 'All day';
                            $a .= "<li><b>".bookingDate($item->event_date, $item->event_date)."</b> - ".$time."</li>";
                        }
                        return '<ul>'.$a.'</ul>';
                        break;

                    case BookableItem::$TYPE_SERVICE:
                        $a = '<li>Service Provider: Aria Living</li>';
                        if ($item->is_free == false && !Auth::user()->isResidentVip()) {
                            $a .= '<li>'.$item->getPriceTag().'</li>';
                        }
                        return '<ul>'.$a.'</ul>';
                        break;
                }
                return '-';
            })
            // Status
            ->addColumn('status', function(BookableItem $item) {
                return $item->getStatus(true);
            })
            // Actions
            ->addColumn('actions', function(BookableItem $item) {

                $actions = '<div class="btn-hspace">
                            <button type="button" data-toggle="dropdown" class="btn btn-sm btn-i btn-primary btn-simple" aria-expanded="true">
                                Actions <i class="material-icons">expand_more</i>
                            </button>
                            <ul role="menu" class="dropdown-menu dropdown-menu-right">';
                                // if(!$item->trashed()) {
                                if(Auth::user()->hasRole(['super-admin', 'admin', 'building-manager'])){
                                    $actions .= '<li>
                                        <form action="'.route('app.item.clone', $item->id).'" method="POST">
                                            '.csrf_field().'
                                            <button type="submit" class="no-btn">Clone item</button>
                                        </form>
                                    </li>';
                                }
                                if(Auth::user()->canDelete()) {
                                    $delete_label = $item->trashed() ? 'Delete permanently' : 'Delete';
                                    $actions .= '<li><a class="actions" type="button" data-target="#mod-delete" href="'.route('app.item.delete', $item->id).'">'.$delete_label.'</a></li>';
                                }
                            $actions .= '
                                </ul>
                        </div>';

                return $actions;
            })
            /** 
             * Column Filer
             */
            ->filterColumn('id', function($query, $keyword) {
                $query->whereRaw("bookable_items.id like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('title', function($query, $keyword) {
                $query->whereRaw("bookable_items.title like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('category', function($query, $category_id) {
                $query->where("bookable_items.category_id", $category_id);
                return;
            })
            ->filterColumn('building', function($query, $building_id) {
                $query->where("bookable_items.building_id", $building_id);
                return;
            })
            ->filterColumn('status', function($query, $status) {
                $query->where("bookable_items.status", $status);
                return;
            })
            // Decode HTML chars
            ->rawColumns([
                'id',
                'title', 
                'category', 
                'building',
                'details',
                'status',
                'actions',
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
                        $query->orderBy("bookable_items.id", $dir);
                        break;

                    case 'title':
                        $query->orderBy("bookable_items.title", $dir);
                        break;

                    case 'category':
                        $query->orderBy("bookable_items.category_id", $dir);
                        break;

                    case 'building':
                        $query->orderBy("building_name", $dir);
                        break;

                    case 'status':
                        $query->orderBy("bookable_items.status", $dir);
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



    /**
     * List the Service Items of a Bookable Item
     *
     * @param  Illuminate\Http\Request $request
     * @return DataTables
     */
    public function lineItemList(Request $request, $bookable_item_id) {

        $items = LineItem::where('item_id', $bookable_item_id);

        $JSON = DataTables::of($items)
            ->addColumn('id', function(LineItem $item) {
                return '<span class="_id">'.$item->id.'</span>';
            })
            ->addColumn('name', function(LineItem $item) {
                return '
                    <span class="row-col title open-data" data-open-item="'.$item->id.'">
                        '.$item->thumbOrInitials().'
                        <span>'.$item->name.'</span>
                    </span>';
            })

            ->addColumn('price', function(LineItem $item) {
                return '<span>'.priceFormat($item->price).'</span>';
            })

            ->addColumn('status', function(LineItem $item) {
                return $item->getStatus();
            })

            ->addColumn('created_date', function(LineItem $item) {
                return '
                <strong>'.dateFormat($item->created_at).'</strong><br>
                <small>'.timeFormat($item->created_at).'</small>';
            })

            ->addColumn('actions', function(LineItem $item) {
                
                $actions = '<div class="btn-hspace">
                            <button type="button" data-toggle="dropdown" class="btn btn-sm btn-i btn-primary btn-simple" aria-expanded="true">
                                Actions <i class="material-icons">expand_more</i>
                            </button>
                            <ul role="menu" class="dropdown-menu dropdown-menu-right">';
                            // Clone
                            $actions .= '<li>
                                <form action="'.route('app.item.lineItems.clone', ['item_id' => $item->item_id, 'line_item_id' => $item->id]).'" method="POST">
                                    '.csrf_field().'<button type="submit" class="no-btn">Clone</button>
                                </form>
                            </li>';
                            // Delete
                            if( Auth::user()->canDelete() ) {
                                $actions .= '<li><a class="actions" data-target="#mod-delete" type="button" href="'.route('app.item.lineItems.delete', ['item_id' => $item->item_id, 'line_item_id' => $item->id]).'">Delete</a></li>';
                            }
                            $actions .= '
                                </ul>
                        </div>';

                return $actions;
            })

            /** 
             * Column Filer
             */
            ->filterColumn('id', function($query, $keyword) {
                $query->whereRaw("line_items.id like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('name', function($query, $keyword) {
                $query->whereRaw("line_items.name like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('price', function($query, $keyword) {
                $query->whereRaw("line_items.price like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('status', function($query, $status) {
                $query->where("line_items.status", $status);
                return;
            })

            /** 
             * Decode HTML chars
             */
            ->rawColumns([
                'id', 
                'name', 
                'price', 
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
                        $query->orderBy("line_items.id", $dir);
                        break;
                    case 'deal':
                        $query->orderBy("line_items.name", $dir);
                        break;
                    case 'store':
                        $query->orderBy("line_items.price", $dir);
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
                    return exportToCSV($data_to_export, 'Service Item List Export '.Carbon::now()->format('md_His').'.csv');
                }
            }

            return $JSON->toJson();
    }

}
