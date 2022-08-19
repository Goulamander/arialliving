<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\Booking;
use App\Models\BookableItem;


class ViewBookingComposer
{
    
    /**
     * The ID of the Location
     * @var int
     */
    protected $booking_id;



    /**
     * Instantiate a new instance of the Composer
     *
     * @param Illuminate\Http\Request
     */
    public function __construct(Request $request)
    {
        $this->booking_id = $request->booking_id;
    }


    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view) {

        $booking = Booking::where('id', $this->booking_id)
            ->with('event')
            ->with('user')
            ->with('user.building')
            ->with('user.role:id,display_name')
            ->with('building')
            ->with('bookableItem')
            ->with('transactions')
            ->myBooking()
            ->withTrashed()
            ->first();

        if( !$booking ) {
            abort(404);
        }

        $booking->bookableItem->images = $booking->bookableItem->getGalleryImages();

        // Grab the 30 most recent comments
        $booking->comments =  $booking->get_comments(0, 30);

        // Add in attendees form other bookings
        $total_attendees = 0;
        $total_paid = 0;

        // Event: Grab and add all attendees for this event
        if( $booking->isEvent() ) {

            $otherBookingsForThisEvent = Booking::where('bookable_item_id', $booking->bookableItem->id)
                ->where('id', '!=', $booking->id)
                ->where('status', '!=', Booking::$STATUS_CANCELED)
                ->with('event')
                ->with('user')
                ->get();

            $booking->attendees = $otherBookingsForThisEvent;

            if($booking->attendees) {
                foreach($booking->attendees as $a) {
                    // $total_attendees += $a->event->attendees_num;
                    if($a->status == Booking::$STATUS_ACTIVE) {
                        $total_attendees += $a->event->attendees_num;
                    }
                    $total_paid += $a->total;
                }
            }
        }

        // Service: Add the Ordered items
        if($booking->isService() && $booking->line_items) {

            $line_items = json_decode($booking->line_items);

            foreach($line_items as $line_item) {
                $line_item->name = $booking->bookableItem->line_items->first(function($it) use($line_item) {
                    return $it->id == $line_item->id;
                })->name;
            }
            $booking->line_items = $line_items;
        }
        
        if($booking->bookableItem->type === BookableItem::$TYPE_ROOM) {
            $booking->cleaning_label = 'Yes';
            if ($booking->other_fee && count($booking->other_fee) > 0) {
                foreach ($booking->other_fee as $fee) {
                    if ((float) $fee['fee'] == 0) {
                        $booking->cleaning_label = 'No';
                    }
                }
            }
            if ($booking->isAdminCleaningRequired()) {
                $booking->cleaning_label = 'Yes';
            }
            if ($booking->isAdminNoCleaningRequired()) {
                $booking->cleaning_label = 'No';
            }
        }


        // Send the data to the view
        $view->with(compact('booking', 'total_attendees', 'total_paid'));
    }

}
