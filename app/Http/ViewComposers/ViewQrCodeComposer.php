<?php

namespace App\Http\ViewComposers;

use Auth;
use Illuminate\Http\Request;
use Illuminate\View\View;

use App\Models\QrCode;

class ViewQrCodeComposer
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

        $qr_code = QrCode::find($this->id);

        // Only SuperAdmins can edit the users.
        $can_edit = Auth::user()->isSuperAdmin() ? true : false;

        // Send the data to the view
        $view->with(compact('qr_code', 'can_edit'));
    }
}
