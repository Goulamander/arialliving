<?php

namespace App\Http\ViewComposers;

use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\BookableItem;
use Storage;
use Auth;

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

        $item = BookableItem::where('id', $this->item_id)
            ->with('category')
            ->with('room')
            ->with('hire')
            ->with('event')
            ->with('recurring')
            ->with('service')
            ->with('building')
            ->with('line_items')
            ->first();

        if(!$item) {
            abort('404');
        }

        // Add files
        $item->images = $item->getGalleryImages();
        $item->thumbs = $item->getGalleryThumbs();
        $item->terms  = $item->getPDFTerms();

        // Grab the 30 most recent comments
        $item->comments =  $item->get_comments(0, 30);


        // convert office hours to array
        if($item->office_hours) {
            $item->office_hours = json_decode($item->office_hours, true);
        }

        if($item->isService() && $item->service && $item->service->hide_cart_functionality) {
            $item->hide_cart_functionality = $item->service->hide_cart_functionality;
        }

        // Send the data to the view
        $view->with(compact('item'));
    }

}
