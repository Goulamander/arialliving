<?php

namespace App\Http\ViewComposers\Resident;

use Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\BookableItem;

use File;
use Storage;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

use Illuminate\Support\Str;


class ViewItemComposer
{
    
    protected $item_id;
    protected $item_type;



    /**
     * Instantiate a new instance of the Composer
     *
     * @param Illuminate\Http\Request
     */
    public function __construct(Request $request)
    {
        $this->item_id = $request->item_id;
        $this->item_type = $request->type;
    }



    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view) {

        $item = BookableItem::findOrFail($this->item_id);

        // Add the images
        $item->images = $item->getGalleryImages(); 


        // Get the Unavailable dates
        $disabled_dates = [];
        $dates = null;
        
        if( in_array($item->type, [BookableItem::$TYPE_ROOM, BookableItem::$TYPE_HIRE]) ) {

            // for this Month
            $from = Carbon::now()->format('Y-m-d');
            $to = Carbon::now()->addWeeks(6)->format('Y-m-d');

            //
            $dates = $item->getUnavailableDates($from, $to);
        }



        $tags = [];

        switch($item->type) {

            case BookableItem::$TYPE_ROOM:

                // Max length per booking
                if($item->room->booking_max_length) {
                    $tags[] = $this->_maxBookingTag($item->room->booking_max_length);
                }

                // booking from - to
                if($item->room->booking_from_time) { 
                    $tags[] = 'Bookings between '.Carbon::parse($item->room->booking_from_time)->format('h:i a').' - '.Carbon::parse($item->room->booking_to_time)->format('h:i a');
                }
                break;

            case BookableItem::$TYPE_HIRE:

                // Max length per booking
                if($item->hire->booking_max_length) {
                    $tags[] = $this->_maxBookingTag($item->hire->booking_max_length);
                }

                // booking from - to
                if(!$item->ignore_office_hours) {
                    $tags[] = 'Pickup/Drop-off between Office Hours';
                }
                break;

            case BookableItem::$TYPE_EVENT:

                // rsvp
                if($item->event->is_rsvp) {
                    $tags[] = 'RSVP';
                }
                if($item->allow_guests && $item->allow_guests > 0) {
                    $tags[] = $item->allow_guests.' Guests allowed / booking';
                }
                break;

        }

        $item->tags = $tags;



        // Grab the Resident's building
        $building = $item->building;
        if($building) {
            $building->office_hours = json_decode($building->office_hours);
        }

        $user = Auth::user();

        // Send the data to the view
        $view->with(compact('item', 'dates', 'building', 'user'));
    }




    /**
     * Create max Booking length tag
     */
    private function _maxBookingTag($length) {

        $booking_unit = 'hr';

        if($length >= 24) {
            $booking_unit = 'day';
            $length = floor($length / 24);
        }

        return 'Max. '.$length.' '.Str::plural($booking_unit, $length).' / booking';
    }




}
