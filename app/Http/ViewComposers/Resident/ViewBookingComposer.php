<?php

namespace App\Http\ViewComposers\Resident;

use Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\Setting;
use App\Models\Booking;
use App\Models\BookableItem;

use Storage;

class ViewBookingComposer
{
    
    protected $booking_id;

    /**
     * Instantiate a new instance of the Composer
     *
     * @param Illuminate\Http\Request
     */
    public function __construct(Request $request) {
        $this->booking_id = $request->booking_id;
    }



    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view) {

        $booking = Booking::findOrFail($this->booking_id);

        // Send the data to the view
        $view->with(compact('booking'));

    }

}
