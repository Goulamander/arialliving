<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ComposersServiceProvider extends ServiceProvider
{
    /**
     * Register Bindings in the container.
     *
     * @return void
     */
    public function boot()
    {

        View::share('SCR_TITLE', 'Aria Living');


        /*
        |--------------------------------------------------------------------------
        | Attach the ViewComposer to Views
        |--------------------------------------------------------------------------
        |
        |
        */

        /** Resident */

        // Admin Controller
        View::composer([
            'resident.adminController',
        ], 'App\Http\ViewComposers\BuildingSwitchComposer');


        // _Dashboard (home page), My Bookings list
        View::composer([
            'resident.index',
            'resident.search',
            'resident.bookings.index',
        ], 'App\Http\ViewComposers\Resident\ViewHomeComposer');

        //
        View::composer([
            'resident.profile.show',
        ], 'App\Http\ViewComposers\Resident\ViewProfileComposer');
        
        View::composer([
            'resident.building.show',
        ], 'App\Http\ViewComposers\Resident\ViewBuildingComposer');
        
        View::composer([
            'resident.deals.index',
            'resident.deals.show',
        ], 'App\Http\ViewComposers\Resident\ViewDealsComposer');
        


        // _Make a booking (item single page) 
        View::composer([
            'resident.item.show'
        ], 'App\Http\ViewComposers\Resident\ViewItemComposer');


        
        /** Admin **/

        // Calendar
        View::composer([
            'app.index'
        ], 'App\Http\ViewComposers\ViewCalendarComposer');

        // User
        View::composer([
            'app.user.show'
        ], 'App\Http\ViewComposers\ViewUserComposer');

        // QR Code
        View::composer([
            'app.qr-code.show'
        ], 'App\Http\ViewComposers\ViewQrCodeComposer');

        // Resident
        View::composer([
            'app.resident.show',
            'app.resident.show.bookings',
            'app.resident.show.archiveBookings',
            'app.resident.show.comments',
        ], 'App\Http\ViewComposers\ViewResidentComposer');

        // Building
        View::composer([
            'app.building.show'
        ], 'App\Http\ViewComposers\ViewBuildingComposer');
        
        // Bookable Items (Room, Event, Hire, Service)
        View::composer([
            'app.item.show'
        ], 'App\Http\ViewComposers\ViewItemComposer');

        // Retail Store
        View::composer([
            'app.store.show'
        ], 'App\Http\ViewComposers\ViewRetailStoreComposer');
    
        // Booking
        View::composer([
            'app.booking.show'
        ], 'App\Http\ViewComposers\ViewBookingComposer');

        // Setting
        View::composer([
            'app.settings.index'
        ], 'App\Http\ViewComposers\Setting\AppSettingComposer');

        // Profile
        View::composer([
            'app.profile.show',
        ], 'App\Http\ViewComposers\ViewProfileComposer');

        // _marketing-communications
        View::composer([
            'app.marketing-communications.show'
        ], 'App\Http\ViewComposers\ViewMarketingCommunicationsComposer');

    }



    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
