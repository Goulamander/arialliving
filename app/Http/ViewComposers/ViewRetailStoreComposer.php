<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

use DB;
use App\Models\RetailStore;


class ViewRetailStoreComposer
{
    
    /**
     * The ID of the Location
     * @var int
     */
    protected $store_id;



    /**
     * Instantiate a new instance of the Composer
     *
     * @param Illuminate\Http\Request
     */
    public function __construct(Request $request)
    {
        $this->store_id = $request->store_id;
    }



    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view) {

        $store = RetailStore::where('id', $this->store_id)
            ->withTrashed()
            ->firstOrFail();
        
        // Add files
        $store->images = $store->getGalleryImages();
        $store->thumbs = $store->getGalleryThumbs();

        $view->with(compact('store'));
    }

}
