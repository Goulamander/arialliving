<?php

namespace App\Http\ViewComposers\Resident;

use Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\Building;

use Storage;

class ViewDealsComposer
{
    
  
    /**
     * Instantiate a new instance of the Composer
     *
     * @param Illuminate\Http\Request
     */
    public function __construct(Request $request) {
        
    }



    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view) {
        
        $building = Building::getOrSetBuilding(['retailDeals', 'retailDeals.store:id,name,thumb']);

        // Get the User's redeemed deals
        $redeemed_deal_ids = [];

        $deal_ids = Auth::user()->redeemedDeals->pluck('id')->toArray();

        if($deal_ids) {
            $redeemed_deal_ids = array_count_values($deal_ids);
        }       

        if($building->retailDeals) {
            foreach($building->retailDeals as $deal) {
                $deal->redeem_num = isset($redeemed_deal_ids[$deal->id]) ? $redeemed_deal_ids[$deal->id] : 0;
                
                $deal->is_hidden = $deal->allowed_redeem_num && ($deal->redeem_num >= $deal->allowed_redeem_num) ? true : false;
            }
        }

        if(! $building ) {
            abort(404);
        }

        $view->with(compact('building'));

    }

}
