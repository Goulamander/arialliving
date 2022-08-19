<?php

namespace App\Http\ViewComposers\Resident;

use Auth;
use Route;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

use App\Models\Category;
use App\Models\BookableItem;
use App\Models\Building;
use App\Models\User;

use Storage;
use Session;

class ViewHomeComposer
{
    protected $search_param;
    

    /**
     * Instantiate a new instance of the Composer
     *
     * @param Illuminate\Http\Request
     */
    public function __construct(Request $request) {
        $this->search_param = $request->input('search');
    }



    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view) {

        // Get the building for the authenticated user
        $building = Building::getOrSetBuilding();
        $user = Auth::user();

        if(! $building ) {
            $message = '';
            if($user->isExternal()) {
                $message = 'Please assign 3rd Party access user to a building first.';
            }
            abort(404, $message);
        }

        $building->image = $building->getGalleryImages(true);
        
        // _get items
        $items = BookableItem::where(function($q) use($building) {
                $q->where(['building_id' => $building->id, 'status' => 1]);
                if(Auth::user()->isResident()) {
                    // todo: replace this with the resident level settings...
                    $q->where('is_private', 0);
                }
                if($this->search_param) {
                    $q->where('title', 'like', "%{$this->search_param}%");
                }
            })->get();


        // add the thumb url
        foreach($items as $item) {
            $item->thumb = $item->is_thumb ? $item->getThumb('820x500') : '';
            $item->type_label = BookableItem::$TYPE_LABEL[$item->type];
            $item->price_html = $item->getPriceTag(true);
        }

        // Grab the categories
        $categories = Category::where('status', 1)->orderBy('order')->get();

        $sliders = [];

        foreach($categories as $category) {
            $sliders[str_slug($category->name)] = [
                'title' => $category->name,
                'items' => $items->where('category_id', $category->id)//->values()
            ];
        }

        // $sliders = json_decode(json_encode($sliders));

        /** Add the user's active bookings if any */
        // $bookings = Auth::user()->activeBookings;
        $bookings = Auth::user()->activeBookingsFromNow;
        
        // Send the data to the view
        $resident_building = $building;

        $view->with(compact('resident_building', 'sliders', 'bookings'));
    }

}
