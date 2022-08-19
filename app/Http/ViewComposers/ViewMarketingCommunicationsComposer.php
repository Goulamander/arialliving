<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\MarketingCommunications;

class ViewMarketingCommunicationsComposer
{

    /**
     * The user's id
     * @var int
     */

    protected $user_id;


    /**
     * Instantiate a new instance of the Composer
     *
     * @param Illuminate\Http\Request  $request
     * @param App\Services\PaginationService
     */

    public function __construct(Request $request)
    {
        $this->id = $request->id;
    }


    /**
     * Bind data to the view
     *
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = MarketingCommunications::find($this->id);

        if($data) {
            $data->receiver = explode(',', $data->receiver);
        }

        // Only SuperAdmins can edit the users.
        $can_edit = Auth::user()->isSuperAdmin() ? true : false;

        // Send the data to the view
        $view->with(compact('data', 'can_edit'));
    }
}
