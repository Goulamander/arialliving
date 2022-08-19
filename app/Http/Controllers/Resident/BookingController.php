<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\BaseClasses\Controller;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

use Illuminate\Encryption\Encrypter;

use App\Http\Requests\Resident\StoreBookingRequest;

use App\Models\User;
use App\Models\Building;
use App\Models\Booking;
use App\Models\BookableItem;
use App\Models\BookableItem\BookableItemEvent;
use App\Models\Comment;
use App\Models\Transaction;
use App\Models\Cart;
use App\Models\LineItem;

use DataTables;
use DB;
use Auth;
use Storage;

use Carbon\Carbon;
use Carbon\CarbonInterval;


class BookingController extends Controller
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
     * Display the Building List
     *
     * @return Response
     */
    public function index() {  
        return view(Route::currentRouteName());
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
     * @return Response/Json
     */
    public function get($booking_id, $with_relations = true) {

        if(!$booking_id) {
            return response()->json([
                'error' => 'No booking_id provided',
                'data' => []
            ], 400);
        }

        if(!$with_relations) {
            $Booking = Booking::where(['id' => $booking_id, 'user_id' => Auth::id()])->first();
        }
        else {
            $Booking = Booking::where(['id' => $booking_id, 'user_id' => Auth::id()])
                ->with('event')
                ->with('user')
                ->with('user.role:id,display_name')
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
                ->with('comments')
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
                'error' => 'Booking with the provided id not found',
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

        // Add the deposit info
        $Booking->deposit_info = __('messages.deposit_info');
        $Booking->payment_note_sub = __('messages.booking.payment_note_sub');

        // Card details
        if($Booking->user->card_details) {

            $card = json_decode($Booking->user->card_details);
            
            $expiry_in = $Booking->user->getCardExpiry();
            $expiry_in_str = "in {$expiry_in} ".Str::plural('days', $expiry_in);
            $exp_soon_class = '';
            
            if($expiry_in <= 60) {
                $exp_soon_class = ' badge-danger';
            }
            if($expiry_in < 0) {
                $expiry_in_str = "Expired";
            }

            $Booking->user->card = [
                'type' => $card->type,
                'type_slug' => Str::slug($card->type),
                'end' => $card->end,
                'exp_month' => $card->exp_month,
                'exp_year' => $card->exp_year,
                'expiry_in_str' => $expiry_in_str,
                'exp_soon_class' => $exp_soon_class
            ];
        }
            
        // add the calendar config to the booking object
        $Booking->attachCalendarConfig();

        return response()->json([
            'error'     => '',
            'data_id'   => $booking_id,
            'data'      => $Booking
        ], 200);
    }



    /**
     * Create a Booking
     *
     * @param App\Http\Requests\Resident\StoreBookingRequest $request
     * @param int $item_type
     * @param int $item_id
     * 
     * @return Response
     */
    public function create(StoreBookingRequest $request, $type, $item_id) {

        // Get the user
        $user = Auth::user();

        // Get the bookable item
        $item = BookableItem::find($item_id);

        // _default 
        $booking_start = null;
        $booking_end = null;

        /** Stage 1: Sort ot the dates per booking type */

        /**
         * Validate the dates for this booking.
         */
        if(in_array($item->type, [BookableItem::$TYPE_ROOM, BookableItem::$TYPE_HIRE]) ) {

            $booking_start = Carbon::parse($request->date_start.' '.$request->time_start);
            $booking_end   = Carbon::parse($request->date_end.' '.$request->time_end);

            $is_booking_dates_valid = $item->validateDatesForBooking($request->date_start, $request->date_end, [$request->time_start, $request->time_end]);

            /** Conflict with dates: This can happen when someone else is making a booking for this item during the checkout process which dates are conflicting whit this booking. (no time holding implemented) */
            if(!$is_booking_dates_valid) {
                return response()->json([
                    'error' => __('messages.booking.date_conflict')
                ], 400);
            }
        }

        // Service Order: validate date
        if($item->type == BookableItem::$TYPE_SERVICE) {
            if($item->service->is_date) {
                // $booking_start = $request->date_start;
                // $booking_end = $request->date_end;
                $booking_start = Carbon::parse($request->date_start.' '.$request->time_start);
                $booking_end   = Carbon::parse($request->date_end.' '.$request->time_end);
            } else {
                $booking_start = Carbon::today();
                $booking_end   = Carbon::today();
            }
        }

        // build the booking start/end from the event dates when booking type is event
        if($item->type == BookableItem::$TYPE_EVENT) {
            $booking_start = $item->event->event_date.' '.$item->event->event_from;
            $booking_end   = $item->event->event_date.' '.$item->event->event_to;
        }
        

        /** Stage 2: Sort ot the payment per booking type */

        
        /**
         * Calculate the total, and process transaction 
         */
        // $is_calulate_total = (!$item->isFree() && !Auth::user()->isResidentVip() && !$item->isFreeAsAdmin()) || $item->isPaymentToAria();
        $is_calulate_total = $item->is_free == false && !Auth::user()->isResidentVip() && !$item->isFreeAsAdmin() && $item->isPaymentToAria();
        if($is_calulate_total) {
            $bond = null;

            // Calc the total / booking type
            switch($item->type) {

                // R O O M (one time admin fee)
                case BookableItem::$TYPE_ROOM:
                    $subtotal = $item->admin_fee;
                    if($item->bookableItemFees()->exists()) {
                        $booking_cleaning_fee = json_decode($request->booking_cleaning_fee, true);
                        if ($booking_cleaning_fee) {
                            $other_fee = [];
                            $subtotal = $subtotal + (float) $booking_cleaning_fee['fee'];
                            $other_fee[] = $booking_cleaning_fee;
                        }
                    }
                    $total = $subtotal;
                    break;

                // E V E N T (attendees * admin fee)
                case BookableItem::$TYPE_EVENT:
                    $attendees_num = isset($request->attendees_num) ? $request->attendees_num : 1;
                    $subtotal = $attendees_num * $item->admin_fee;
                    $total = $subtotal;
                    break;

                // H I R E ((qty * (item_price * booking_length)) + security bong)
                case BookableItem::$TYPE_HIRE:
                    // Get the length of the booking in hours
                    $booking_length = $this->_getBookingLength($booking_start, $booking_end, $item->hire->item_price_unit) ?? 1;
                    //
                    $subtotal = ($request->_qty * $item->hire->item_price) * $booking_length;
                    //
                    $total = $subtotal;
                    //
                    if($item->hire->bond_amount && $item->hire->bond_amount > 0) {
                        $bond = $item->hire->bond_amount;
                    }
                    break;


                // S E R V I C E (line_item price * qty) * line_items + admin_fee
                case BookableItem::$TYPE_SERVICE:

                    // Get the Cart items here
                    $cart_items = json_decode($item->cart->items);

                    if(!$cart_items) {
                        return response()->json([
                            'error' => 'Your Cart is empty.'
                        ], 400);
                    }
               
                    // attach the line-item
                    foreach($cart_items as $cart_item) {
                        $cart_item->item = $item->line_items->first(function($it) use($cart_item) {
                            return $it->id == $cart_item->id;
                        });
                    }

                    $subtotal = $item->calculateSubTotal($cart_items);

                    // _add the admin fee, if any
                    $total = $subtotal;

                    if($item->admin_fee && $item->admin_fee > 0) {
                        $total = $subtotal + $item->admin_fee;
                    }

                    if($item->service->bond_amount && $item->service->bond_amount > 0) {
                        $bond = $item->service->bond_amount;
                    }
                    break;
            }


            /** -- transactions -- */
            
            // Create or update the resident's card
            if( $request->input('card_name') ) {

                $create_card_response = $user->storeCreditCard($request->all());

                if( $create_card_response['status'] === true ) {
                    $user->tokenCustomerID = $create_card_response['tokenCustomerID'];
                }
                else {
                    return response()->json([
                    	'error' => $create_card_response['errors']
                    ], 400);
                }
            }

            // Make direct payment for Service Orders
            if($item->type == BookableItem::$TYPE_SERVICE && $item->isPaymentToAria()) {
                
                $direct_transaction = $user->makeDirectTransaction($total);

                if($direct_transaction['status'] === false) {
                    return response()->json([
                    	'error' => $direct_transaction['errors']
                    ], 400);
                }
            }


        } // is_free -> END


        /** Stage 3: Create the booking, and store the booking type specific details */
        // is_free condition 
        $is_free = $item->is_free || !!Auth::user()->isResidentVip()  || !!$item->isFreeAsAdmin() || !$item->isPaymentToAria();


        $ResidentBooking = Booking::create([
            'user_id' => Auth::id(),
            'building_id' => $item->building_id,
            'bookable_item_id' => $item->id,
            'type' => $item->type,
            'status' => 1, // active
            'start' => $booking_start,
            'end'   => $booking_end,
            'length_str' => $this->_getBookingLengthStr($booking_start, $booking_end),
            'qty'       => $request->input('booking_qty', 1),
            'subtotal'  => $is_free ? NULL : $subtotal,
            'GST'       => $is_free ? NULL : number_format( (float) $total/11 , 2, '.', ''),
            'bond'      => $is_free ? NULL : $bond,
            'admin_fee' => $is_free ? NULL : $item->admin_fee,
            'total'     => $is_free ? NULL : $total,
            'accepted_terms' => $request->accepted_terms ? json_encode(json_decode($request->accepted_terms)) : NULL,
            'signature' => $request->signature ? encrypt(json_encode($request->signature)) : NULL,
            'booking_comments' => $request->input('booking_comments', NULL),
            'cancellation_cutoff_date' => $item->getCutOffDate($booking_start),
            'other_fee' => $other_fee ?? NULL,
            'cleaning_required' => $request->cleaning_required ?? NULL,
        ]);

        /** Store the Booking type specific details */

        /**
         * Service Orders:
         * Save the line items form the Cart -> once the items are saved empty the cart.
         */
        if( $item->isService() ) {
  
            // add price to the line items
            $cart_items = json_decode($item->cart->items);

            foreach($cart_items as $cart_item) {
                $cart_item->price = $item->line_items->first(function($it) use($cart_item) {
                    return $it->id == $cart_item->id;
                })->price;
            }

            $ResidentBooking->line_items = json_encode($cart_items);
            $ResidentBooking->save();

             // delete cart
            $item->cart->forceDelete();
        }

        /**
         * for Hire bookings
         *  - store the hire item and its price as line item, to avoid using prices from the bookable item. 
         */
        if( $item->isHire() ) {

            $ResidentBooking->line_items = json_encode([
                'price' => $item->hire->item_price,
                'price_unit' => $item->hire->item_price_unit,
            ]);
            $ResidentBooking->save();
        }

        /**
         * for Event bookings
         * store the number of attendees, and their email addresses for rsvp todo:
         */
        if( $item->isEvent() ) {
            $ResidentBooking->event()->create([
                'attendees_num' => isset($request->attendees_num) ? $request->attendees_num : 1,
                'booking_status' => 1 // todo: do we need this here?
            ]);
        }


        // Create the transaction (for Service Orders only)
        if( isset($direct_transaction['response']) && $item->type == BookableItem::$TYPE_SERVICE && $item->isPaymentToAria()) {

            $transaction = Transaction::create([
                'booking_id' => $ResidentBooking->id,
                'type' => Transaction::$TYPE_DIRECT,
                'responseCode' => $direct_transaction['response']->ResponseCode,
                'responseMessage' => $direct_transaction['response']->ResponseMessage,
                'transactionID' => $direct_transaction['response']->TransactionID,
                'transactionStatus' => $direct_transaction['response']->TransactionStatus,
                'totalAmount' => $total,
            ]);

            // Payment Success or Failed?
            $ResidentBooking->status = $direct_transaction['response']->TransactionStatus == true ? Booking::$STATUS_CONFIRMED : Booking::$STATUS_PAYMENT_FAILED;
            $ResidentBooking->save();
        }
        

        $ResidentBooking->load('user:id,first_name,last_name,email');
        $ResidentBooking->load('bookableItem:id,title,is_thumb,is_free,price_tag,booking_instructions');
        $ResidentBooking->load('bookableItem.hire:bookable_item_id,item_price,item_price_unit');

        if( !$ResidentBooking ) {
            return response()->json([
                'error' => __('create_error')
            ], 400);
        }

        // Send the formatted dates and times
        $ResidentBooking->bookingDate = bookingDate($ResidentBooking->start, $ResidentBooking->end);
        $ResidentBooking->bookingTime = bookingTime($ResidentBooking->start, $ResidentBooking->end);


        // Send the Booking Conformation to Resident
        \Illuminate\Support\Facades\Log::info("Booking #{{$ResidentBooking->id}}. by: {{$ResidentBooking->user->email}}");
        $ResidentBooking->sendBookingConfirmation();


        // Live UI update: Refresh admin calendar
        // broadcast(new LocationUpdated($Booking))->toOthers();

        $redirect_to = $ResidentBooking->bookable_item_id;

        if(!Auth::user()->isResident()) {
            // redirect to the back-end booking manager
            $redirect_to = route('app.booking.show', $ResidentBooking->id);

            return response()->json([
                'error' => '',
                'data' => compact(['ResidentBooking']),
            ], 200);
        }

        return response()->json([
            'error' => '',
            'data' => compact(['ResidentBooking']),
            'message' => 'Booking successful!'
        ], 200);

    }




    /**
     * Update a Booking
     *
     * @param App\Http\Requests\Resident\StoreBookingRequest $request
     * @param int $booking_id
     * @return Response
     * 
     */
    public function update(Request $request, $booking_id) {
        $user = Auth::user();

        // $Booking = Booking::where(['id' => $booking_id, 'user_id' => $user->id])->first();
        $Booking = Booking::where(function ($query) use($user, $booking_id) {
            $query->where('id', $booking_id);
            if($user->isResident() || $user->isResidentVip()){
                $query->where('user_id', $user->id);
            }
        })->first();
        
        // Booking cannot be find
        if(!$Booking) {
            return response()->json([
                'error' => 'There is no booking found with the provided Booking Id.'
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


        /** Stage 1: Sort ot the dates per booking type */

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
         * 
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
            // 'comments' => $request->input('booking_comments', NULL),
            'cancellation_cutoff_date' => $item->getCutOffDate($booking_start),
            'cleaning_required' => isset($request->cleaning_required) ? $request->cleaning_required : NULL,
            'booking_comments' => isset($request->booking_comments) ? $request->booking_comments : $Booking->booking_comments
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
        

        // Live UI update for the back-end users
        // broadcast(new LocationUpdated($Booking))->toOthers();

        return response()->json([
            'error' => '',
            'data' => compact(['Booking']),
            'message' => __('messages.booking.update_success', ['booking_id' => $Booking->getNumber()])
        ], 200);
    }



    /**
     * Calculate the length of the booking, it returns in the item price unit format (day or hour)
     * 
     * @param Carbon $start
     * @param Carbon $end
     * @param Str $item_price_unit
     * 
     * @return num $length
     */
    function _getBookingLength($start, $end, $item_price_unit) {

        $length = $start->diffInHours($end);

        if($item_price_unit == "day") 
        {
            $length = $length / 24;
            // adjust length (for days, the smallest unit is .5)
            $length = $length <= 0.5 ? 0.5 : (round($length * 2) / 2);
        }

        return $length;
    }


    /**
     * Calculate the length of the booking, it returns in str format
     * 
     * @param Carbon $start
     * @param Carbon $end
     * 
     * @return str $length
     */
    function _getBookingLengthStr($start, $end) {

        if( !$start || !$end ) {
            return null;
        }

        $length = Carbon::parse($start)->diffInMinutes(Carbon::parse($end));
        return CarbonInterval::minutes($length)->cascade()->forHumans(['short' => true]);
    }



    /**
     * Cancel a booking
     */
    public function cancel($bookingID) {

        if( Auth::user()->isResident() ) {
            
            $Booking = Booking::where('id', $bookingID)
                        ->where('user_id', Auth::id())
                        ->first();
        }
        else {

            $Booking = Booking::where('id', $bookingID)
                        ->myBooking()
                        ->first();
        }

        if(!$Booking) {
            return response()->json([
                'error' => 'Booking with the provided number cannot be found.'
            ], 400);
        }
    
        // Check Status
        if($Booking->status != Booking::$STATUS_ACTIVE) {

            // Not valid for cancellation
            return response()->json([
                'error' => 'Booking with the provided number cannot be cancelled.'
            ], 400);
        }

        // Run the cancel flow
        $Booking->cancel();

        return response()->json([
            'error' => '',
            'message' => __('messages.booking.cancel_success', ['booking_id' => $Booking->getNumber()]),
            'data' => [
                'id' => $Booking->id
            ]
        ], 200);
    }
    




    /**
     * List Bookings and return in DataTables.
     *
     * @param  Illuminate\Http\Request $request
     * @return DataTables
     */

    public function List(Request $request)
    {
 
        $user = Auth::user();

        $items = Booking::with('event')
            ->with('bookableItem:id,title,is_thumb')
            ->with('bookableItem.category:id,name')
            ->where('user_id', $user->id);

        return DataTables::of($items)
            //
            ->addColumn('id', function(Booking $booking) {
                return '<span class="_id">'.$booking->getNumber().'</span>';
            })
            // Title
            ->addColumn('title', function(Booking $booking) {

                $booking->thumb = $booking->bookableItem->is_thumb ? $booking->bookableItem->getThumb() : '';

                $a = '<span class="row-col title">';
                if($booking->thumb) {
                    $a .= '<span class="initials _bg" style="background-image: url('.$booking->thumb.')"></span>';
                }
                $a .= '
                    <small>'.$booking->bookableItem->category->name.'</small>
                    <b>'.$booking->bookableItem->title.'</b>
                </span>';
                return $a;
            })
            // Dates
            ->addColumn('date_time', function(Booking $booking) {
                // _service
                if($booking->type == Booking::$TYPE_SERVICE) {
                    if($booking->start) {
                        return '
                        <small>'.$booking->bookableItem->service->date_field_name.'</small>
                        <span class="date">'.bookingDate($booking->start, $booking->end).'</span>';
                    }
                    return '';
                }
                // _event
                if($booking->type == Booking::$TYPE_EVENT) {
                    if($booking->start) {
                        $event_from = $booking->bookableItem->event->event_from ? timeFormat($booking->bookableItem->event->event_from) . ' - ' : 'All day';
                        $event_to = $booking->bookableItem->event->event_to ? timeFormat($booking->bookableItem->event->event_to) : '';
                        return '
                        <span class="date">'.$booking->showEventDate().'</span>
                        <span class="time">'.$event_from.$event_to.'</span>';
                        // <span class="time">'.$booking->showEventTime().'</span>';
                    }
                    return '';

                }
                // _all other
                return '
                <span class="date">'.bookingDate($booking->start, $booking->end).'</span>
                <span class="time">'.bookingTime($booking->start, $booking->end).'</span>';
            })
            //
            ->addColumn('total', function(Booking $booking) {
                if($booking->total) {
                    $bond_amount = null;
                    if($booking->bond) {
                        $bond_amount = '<small>Deposit: '.priceFormat($booking->bond).'</small>';
                    }
                    return '<span class="row-col"><span class="price">' . priceFormat($booking->subtotal) . '</span>'.$bond_amount.'</span>';
                }
                return "-";
            })
            //
            ->addColumn('status', function(Booking $booking) {
                return $booking->statusLabel();
            })
            //
            ->addColumn('actions', function(Booking $booking) {
                if( $booking->status == 1 ) {
                    return '<button type="button" data-open-booking="'.$booking->id.'" class="btn btn-primary btn-simple btn-sm">Manage booking</button>';
                }
                return '<button type="button" data-open-booking="'.$booking->id.'" class="btn btn-primary btn-sm">View</button>';
              
            })

            // Decode HTML chars
            ->rawColumns(['id', 'title', 'date_time', 'total', 'status', 'actions'])
            // Order
            ->order(function ($query) {
                $query->orderBy('bookings.id', 'DESC');
                // $query->orderByRaw("
                //     (CASE WHEN bookings.status = 1 THEN start END) ASC,
                //     (CASE WHEN bookings.status != 1 THEN start END) DESC
                // ");
                // (CASE WHEN bookings.status = 1 THEN start END) ASC,
                // (CASE WHEN bookings.status != 1 THEN start END) DESC
            })
            ->make(true);
    }
    



}
