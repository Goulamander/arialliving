<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests\StoreBuildingRequest;

use App\Models\User;
use App\Models\Building;
use App\Models\Booking;
use App\Models\BookableItem;
use App\Models\BookableItem\BookableItemEvent;
use App\Models\Comment;

use DataTables;
use DB;
use Auth;
use Storage;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Support\Arr;


class BookingController extends Controller
{

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
     * Display the Booking List
     *
     * @return Response
     */
    public function index($tab = null) {
        return view(Route::currentRouteName(), compact('tab'));
    }



    /**
     * Building single page view
     *
     * @return Response
     */
    public function show() {
        return view(Route::currentRouteName());
    }


    /**
     * Get a Booking
     *
     * @param  int $booking_id
     * @param  bool $with_relations
     * 
     * @return Response
     */

    public function get($booking_id, $with_relations = true)
    {
        if(!$booking_id) {
            return response()->json([
                'error' => __('messages.invalidData', ['type' => 'Booking ID']),
                'data' => []
            ], 400);
        }

        if(!$with_relations) {
            $Booking = Booking::where('id', $booking_id)->first();
        }
        else {
            $Booking = Booking::where('id', $booking_id)
                ->with('event')
                ->with('user')
                ->with('user.role:id,display_name')
                ->with('user.building')
                ->with('building')
                ->with('bookableItem')
                ->with('bookableItem.category:id,name')
                // service item details
                ->with('bookableItem.service:bookable_item_id,date_field_name,is_date')
                ->with('bookableItem.cart:item_id,items')
                // room item details
                ->with('bookableItem.room:bookable_item_id,allow_multiday')
                // hire item details
                ->with('bookableItem.hire:bookable_item_id,item_price,item_price_unit,bond_amount,allow_multiday')
                // event item details
                ->with('bookableItem.event:bookable_item_id,event_type,event_date,event_from,event_to')
                ->with('bookableItem.recurring:bookable_item_id,repeat_next')
                ->with('bookableItem.event.location:id,title')
                //
                ->with('transactions')
                ->withTrashed()
                ->first();

            // Attach the line items name to the Service Order line-items
            if($Booking->type == Booking::$TYPE_SERVICE && $Booking->line_items) {

                $line_items = json_decode($Booking->line_items);

                foreach($line_items as $line_item) {
                    $line_item->name = $Booking->bookableItem->line_items->first(function($it) use($line_item) {
                        return $it->id == $line_item->id;
                    })->name;
                }
                $Booking->line_items = $line_items;
            }
        }

        // booking not found
        if(!$Booking) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Booking']),
                'data' => []
            ], 400);
        }

        // Normalize the data for the JS templates
        $Booking->number = $Booking->getNumber();
        $Booking->thumb = $Booking->bookableItem->is_thumb ? $Booking->bookableItem->getThumb() : '';

        $Booking->type_label_url = $Booking->typeLabel(true);
        $Booking->type_label = $Booking->typeLabel();
        $Booking->date_formatted = bookingDate($Booking->start, $Booking->end);

        if( in_array($Booking->type, [Booking::$TYPE_ROOM, Booking::$TYPE_HIRE]) ) {
            $Booking->start_time_option = '<option value="'.timeFormat($Booking->start, 'H:i:s').'" data-selected="true">'.timeFormat($Booking->start).'</option>';
            $Booking->end_time_option   = '<option value="'.timeFormat($Booking->end, 'H:i:s').'" data-selected="true">'.timeFormat($Booking->end).'</option>';
        }

        $Booking->user->unit_no = $Booking->user->building[0]->pivot->unit_no ?? '-';

        $Booking->user->is_admin = $Booking->user->isSuperAdmin() || $Booking->user->isAdmin();
            
        if($Booking->user->is_flagged) {
            $Booking->user->flagged_label = User::getFlagLabel($Booking->user->is_flagged_reason);
        }

        /** 
         * add the calendar config to the booking object
         */
        $Booking->attachCalendarConfig();

        return response()->json([
            'error'   => '',
            'data_id' => $booking_id,
            'data'    => $Booking
        ], 200);
    }



    /**
     * Add manual a Booking
     *
     * @param $request
     * @return Response
     */
    public function addManual(Request $request)
    {
        $start = Carbon::parse($request->date_start . ' ' . $request->time_start);
        $end = Carbon::parse($request->date_start . ' ' . $request->time_end);
        $save_data = [
            'user_id'=>$request->user_id ?? Auth::user()->id,
            'start'=>$start,
            'end'=>$end,
            'qty'=>1,
            'cleaning_required'=>$request->cleaning_required,
            'length_str'=>$this->_getBookingLengthStr($start, $end),
            'status'=>1,
        ];

        //===================
        $bookable_item = BookableItem::find($request->bookable_item_id);
        $save_data['building_id'] = $bookable_item->building->id;
        $save_data['type'] = $bookable_item->type;
        $save_data['bookable_item_id'] = $bookable_item->id;
        $save_data['cancellation_cutoff_date'] = $bookable_item->getCutOffDate($start);

        // //====================
        $save_booking = Booking::create($save_data);
        if($save_booking) {
            return response()->json([
                'error' => '',
                'data' => $save_booking,
                'message' => ''
            ], 200);
        } else {
            return response()->json([
                'error' => 'Error'
            ], 400);
        }
    }

    function _getBookingLengthStr($start, $end) {

        if( !$start || !$end ) {
            return null;
        }

        $length = Carbon::parse($start)->diffInMinutes(Carbon::parse($end));
        return CarbonInterval::minutes($length)->cascade()->forHumans(['short' => true]);
    }

    /**
     * Update a Booking
     *
     * @param  Illuminate\Http\StoreBookingRequest $request
     * @param  int $booking_id
     * @return Response
     */

    public function update(StoreBookingRequest $request, $booking_id)
    {
        $Booking = Booking::where('id', $booking_id)->first();
        
        // Booking cannot be find
        if(!$Booking) {
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Booking'])
            ], 400);
        }

        $item = $Booking->bookableItem;

        // Service Order cannot be updated
        if($Booking->type == Booking::$TYPE_SERVICE) {
            return response()->json([
                'error' => 'The Booking with the provided Id cannot be update.'
            ], 400);
        }

        // _default 
        $booking_start = null;
        $booking_end = null;

        /**
         * Validate the dates for this booking.
         */
        if(in_array($item->type, [BookableItem::$TYPE_ROOM, BookableItem::$TYPE_HIRE]) ) {

            $booking_start = Carbon::parse($request->date_start.' '.$request->time_start);
            $booking_end   = Carbon::parse($request->date_end.' '.$request->time_end);

            $is_booking_dates_valid = $item->validateDatesForBooking($request->date_start, $request->date_end, [$request->time_start, $request->time_end], $Booking->id);

            /** Conflict with dates: This can only happen when someone else is making a booking for this item during the checkout process whit conflicting date/time range. (no time holding implemented) */
            if(!$is_booking_dates_valid) {
                return response()->json([
                    'error' => __('messages.booking.date_conflict')
                ], 400);
            }
        }

        // Events: no validation required
        if($item->type == BookableItem::$TYPE_EVENT) {
            $booking_start = $Booking->start;
            $booking_end   = $Booking->end;
        }

        /**
         * Calculate the price changes per booking type
         */
        $is_free = true;
        if($Booking->total && $Booking->total > 0) {

            $is_free = false;

            switch($item->type) {

                // R O O M: The price will not change
                case BookableItem::$TYPE_ROOM:
                    $subtotal = $Booking->subtotal;
                    $total = $Booking->total;
                    break;

                // E V E N T: Price can change when the attendee number changes (attendees * admin fee)
                case BookableItem::$TYPE_EVENT:
                    $subtotal = $request->attendees_num * $Booking->admin_fee;
                    $total = $subtotal;
                    break;

                // H I R E: Price can change based on the new date/time range (qty * (item_price * booking_length)
                case BookableItem::$TYPE_HIRE:
                    // Grab the pricing of the hire item stored in this booking
                    $bookingHireItem = json_decode($Booking->line_items);
                    // Get the length of the booking in hours
                    $booking_length = $this->_getBookingLength($booking_start, $booking_end, $bookingHireItem->price_unit);
                    //
                    $subtotal = ($Booking->qty * $bookingHireItem->price) * $booking_length;
                    $total = $subtotal;
                    break;
            }
        }

        $Booking->update([
            'start' => $booking_start,
            'end'   => $booking_end,
            'length_str' => $this->_getBookingLengthStr($booking_start, $booking_end),
            'subtotal'  => $is_free ? NULL : $subtotal,
            'GST'       => $is_free ? NULL : number_format( (float) $total/11 , 2, '.', ''),
            'total'     => $is_free ? NULL : $total,
            'cancellation_cutoff_date' => $item->getCutOffDate($booking_start),
            'cleaning_required' => isset($request->cleaning_required) ? $request->cleaning_required : NULL
        ]);

        /**
         * for Event bookings
         * store the number of attendees, and their email addresses for rsvp todo:
         */
        if($Booking->type == Booking::$TYPE_EVENT) {
            $Booking->event->update([
                'attendees_num' => $request->attendees_num,
            ]);
        }

        // Send the Booking Update Notification to the Resident
        $Booking->sendBookingConfirmation(true);

        return response()->json([
            'error' => '',
            'data' => compact(['Booking']),
            'message' => __('messages.booking.update_success', ['booking_id' => $Booking->getNumber()])
        ], 200);

    }


    /**
     * Soft delete (Cancel) a booking
     *
     * @param  Illuminate\Http\Request $request
     * @param  int $booking_id
     * @return Response/Json
     */
    public function destroy(Request $request, $booking_id)
    {
        $Booking = Booking::where('id', $booking_id)
            ->myBooking()
            ->first();

        if(!$Booking) {
            // can not found building
            return response()->json([
                'error' => __('messages.notFound', ['type' => 'Booking']),
                'data' => [],
            ], 400);
        }

        // init the cancel flow
        $Booking->cancel();

        return response()->json([
            'error' => '',
            'data' => compact(['Booking']),
            'message' => __('messages.admin.booking.cancel_success', ['booking_id' => $Booking->getNumber()])
        ], 200);
    }



    /**
     * List Bookings and return in DataTables.
     *
     * @param  Illuminate\Http\Request $request
     * @return DataTables
     */

    public function List(Request $request, $tab = '')
    {
        $user = Auth::user();
        $items = Booking::select(DB::raw("
            -- Booking
            bookings.id,
            bookings.type,
            bookings.start,
            bookings.end,
            bookings.qty,
            bookings.bond,
            bookings.total,
            bookings.status,
            bookings.building_id,
            -- User 
            concat(users.first_name, ' ', users.last_name) as user_name,
            users.mobile,
            users.is_flagged as user_is_flagged,
            users.is_flagged_reason as user_is_flagged_reason,
            -- Building
            buildings.name as building_name,
            buildings.is_thumb as building_thumb,
            buildings.suburb as building_suburb,
            -- Bookable Item
            bookable_items.id as item_id,
            bookable_items.title as item_title,
            bookable_items.is_thumb as item_thumb,
            -- Bookable Item / Category
            categories.name as item_category,
            bookable_item_service.date_field_name as service_date_field_name,
            bookable_item_service.assign_to_user_id,
            recurring_event.repeat_start,
            recurring_event.repeat_end,
            recurring_event.repeat_next,
            bookable_item_event.event_type as bookable_item_event_event_type,
            bookable_item_event.event_date as bookable_item_event_event_date,
            bookable_item_event.event_from as bookable_item_event_event_from,
            bookable_item_event.event_to as bookable_item_event_event_to
        "))
        ->leftJoin('users', 'users.id', 'bookings.user_id')
        ->leftJoin('buildings', 'buildings.id', 'bookings.building_id')
        ->leftJoin('bookable_items', 'bookable_items.id', 'bookings.bookable_item_id')
        ->leftJoin('categories', 'categories.id', 'bookable_items.category_id')
        ->leftJoin('bookable_item_service', 'bookable_item_service.bookable_item_id', 'bookings.bookable_item_id')
        ->leftJoin('recurring_event', 'recurring_event.bookable_item_id', 'bookings.bookable_item_id')
        ->leftJoin('bookable_item_event', 'bookable_item_event.bookable_item_id', 'bookings.bookable_item_id')
        ->with('transactions:id,type,transactionID,transactionStatus,totalAmount')
        // validate access
        ->myBooking()
        ->groupBy('bookings.id');

        // User ID provided / filter the results
        if($request->user_id) {
            $items->where('bookings.user_id', $request->user_id);
        }

        if($user->isExternal()){
            // $items->whereRaw('bookable_item_service.assign_to_user_id', $user->id);
            $items->whereRaw(" bookable_item_service.assign_to_user_id = {$user->id} ");
        }
        
        if($tab) {
            switch($tab) {

                case 'active':
                    $items->where('bookings.status', Booking::$STATUS_ACTIVE);
                    break;

                case 'confirmed':
                    $items->where('bookings.status', Booking::$STATUS_CONFIRMED);
                    break;

                case 'require-action':
                    $items->haveBondToRelease();
                    $items->orWhere('bookings.status', Booking::$STATUS_PAYMENT_FAILED);
                    break;

                // case 'archive':
                //     $items->whereIn('bookings.status', [
                //         Booking::$STATUS_COMPLETE, 
                //         Booking::$STATUS_CANCELED,
                //         Booking::$STATUS_ARCHIVE
                //     ])
                //     ->withTrashed();
                case 'complete':
                    $items->where('bookings.status', Booking::$STATUS_COMPLETE);
                    break;
                case 'canceled':
                    $items->whereIn('bookings.status', [
                                Booking::$STATUS_CANCELED,
                                Booking::$STATUS_ARCHIVE
                            ])->withTrashed();
                    break;
            }
        } else {
            $items->withTrashed();
        }

        $JSON = DataTables::of($items)

            // Booking No.
            ->addColumn('id', function(Booking $booking) {
                return '<span class="_id">'.$booking->getNumber().'</span>';
            })

            // Title
            ->addColumn('title', function(Booking $booking) {
                
                $a = '<a href="'.route('app.booking.show', $booking->id).'" class="row-col title">';
                if($booking->item_thumb) {
                    $a .= '<span class="initials _bg" style="background-image: url('.BookableItem::getThumbStatic($booking->item_thumb, $booking->item_id).')"></span>';
                }
                $a .= '
                    <small data-exclude="true">'.$booking->item_category.'</small>
                    <b>'.$booking->item_title.'</b>
                </a>';
                return $a;
            })

            // User (Resident/Admin)
            ->addColumn('user', function(Booking $booking) {

                $flag = null;

                if($booking->user_is_flagged) {
                    $flag = User::getFlagLabel($booking->user_is_flagged_reason);
                }

                return '<span class="row-col">
                    <b>'.$booking->user_name.$flag.'</b>
                    <small data-exclude="true">'.$booking->mobile.'</small>
                </span>';
            })

            // Building
            ->addColumn('building', function(Booking $booking) {
                return '<span class="row-col">
                            '.Building::getThumbOrInitials($booking->building_id, $booking->building_name, $booking->building_thumb) .'
                            <span>'.$booking->building_name.'</span>
                            <small data-exclude="true">'.$booking->building_suburb.'</small>
                        </span>';
            })

            // Dates
            ->addColumn('date_time', function(Booking $booking) {
                // _service
                if($booking->type == Booking::$TYPE_SERVICE) {
                    if($booking->start) {
                        return '
                        <small>'.$booking->service_date_field_name.'</small>
                        <span class="date">'.bookingDate($booking->start, $booking->end).'</span>';
                    }
                    return '';
                } 

                if($booking->type == Booking::$TYPE_EVENT && $booking->bookable_item_event_event_from && $booking->bookable_item_event_event_to && $booking->bookable_item_event_event_date) {
                    if($booking->bookable_item_event_event_type == BookableItemEvent::$TYPE_SINGLE) {
                        $event_date = bookingDate($booking->start, $booking->end);
                    } else {
                        $event_date = bookingDate($booking->repeat_next, $booking->repeat_next);
                    }

                    $event_from = $booking->bookable_item_event_event_from ? timeFormat($booking->bookable_item_event_event_from) . ' - ' : 'All day';
                    $event_to = $booking->bookable_item_event_event_to ? timeFormat($booking->bookable_item_event_event_to) : '';
                    return '
                        <span class="date">'.$event_date.'</span>
                        <span class="time">'.$event_from.$event_to.'</span>';
                }

                if($booking->repeat_start) {
                    return '
                    <span class="date">'.bookingDate($booking->repeat_next, $booking->repeat_next).'</span>
                    <span class="time">'.bookingTime($booking->start, $booking->end).'</span>';
                }
                // _all other
                return '
                <span class="date">'.bookingDate($booking->start, $booking->end).'</span>
                <span class="time">'.bookingTime($booking->start, $booking->end).'</span>';
            })

            // Total
            ->addColumn('total', function(Booking $booking) {
                if($booking->total) {
                    $bond_amount = null;
                    if($booking->bond) {
                        $bond_amount = '<small>Deposit: '.priceFormat($booking->bond).'</small>';
                    }
                    return '<span class="row-col"><span class="price">' . priceFormat($booking->total) . '</span><small data-exclude="true">'.$bond_amount.'</small></span>';
                }
                return "-";
            })

            // Status
            ->addColumn('status', function(Booking $booking) {
                return $booking->statusLabel();
            })
            
            ->addColumn('actions', function(Booking $booking) {

                $actions = '
                <div class="btn-hspace">
                    <button type="button" data-toggle="dropdown" class="btn btn-sm btn-i btn-primary btn-simple" aria-expanded="true">
                        Actions <i class="material-icons">expand_more</i>
                    </button>
                    <ul role="menu" class="dropdown-menu dropdown-menu-right">';
                    if( Auth::user()->canDelete() && !$booking->isCancelled() ) {
                        $actions .= '<li><a class="actions" data-target="#mod-cancel" type="button" href="'.route('app.booking.delete', $booking->id).'">Cancel booking</a></li>';
                    }
                    $actions .= '
                    </ul>
                </div>';

                return $actions;
            })

            // Filter columns
            ->filterColumn('id', function($query, $keyword) {
                $query->where("bookings.id", $keyword);
                return;
            })
            ->filterColumn('title', function($query, $keyword) {
                $query->whereRaw("bookable_items.title  like ?", ["%{$keyword}%"])
                      ->orWhereRaw("categories.name like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('user', function($query, $keyword) {
                $query->whereRaw("concat(users.first_name, ' ', users.last_name) like ?", ["%{$keyword}%"])
                      ->orWhereRaw("users.mobile like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('building', function($query, $keyword) {
                $query->where("bookings.building_id", $keyword);
                return;
            })
            ->filterColumn('total', function($query, $keyword) {
                $query->whereRaw("total like ?", ["%{$keyword}%"]);
                return;
            })
            ->filterColumn('status', function($query, $keyword) {
                $query->where("bookings.status", $keyword);
                return;
            })

            // Decode HTML chars
            ->rawColumns([
                'id',
                'title',
                'user',
                'building',
                'date_time',
                'total',
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
                        $query->orderBy('bookings.id', $dir);
                        break;

                    case 'title':
                        $query->orderBy('item_title', $dir);
                        break;

                    case 'user':
                        $query->orderBy('user_name', $dir);
                        break;

                    case 'building':
                        $query->orderBy('building_name', $dir);
                        break;

                    case 'date_time':
                        $query->orderBy('start', $dir);
                        break;

                    case 'total':
                        $query->orderBy('total', $dir);
                        break;

                    case 'status':
                        $query->orderBy('status', $dir);
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
                    'item_category',
                    'mobile'
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
