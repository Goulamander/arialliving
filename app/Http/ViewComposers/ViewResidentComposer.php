<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\User;


class ViewResidentComposer
{
    
    /**
     * The ID of the Location
     * @var int
     */
    protected $user_id;



    /**
     * Instantiate a new instance of the Composer
     *
     * @param Illuminate\Http\Request
     */
    public function __construct(Request $request) {
        $this->user_id = $request->user_id;
    }



    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view) {

        $resident = User::where('id', $this->user_id)
            ->with('building')
            ->with('bookings')
            ->withTrashed()
            ->firstOrFail();

        // Grab the 30 most recent comments
        $resident->comments =  $resident->get_comments(0, 30);

        // Send the data to the view
        $view->with(compact('resident'));
    }

}
