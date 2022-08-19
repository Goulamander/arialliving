<?php

use Illuminate\Http\Request;
use App\Mail\NormalEmail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;
/*
|--------------------------------------------------------------------------
| App Routes
|--------------------------------------------------------------------------
|
| Here is where you can register app routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
    
    Auth::routes(['register' => false]);
    

    // Activation
    Route::get('user/activation/{token}', 'Auth\ActivateController@activateUser')->name('user.activate');
    Route::post('user/activation/{token}/set-password', 'Auth\ActivateController@setPassword')->name('user.activate.setpassword');



    // Login as User routes
    Route::impersonate();

	/***********************************************************************/
	/**
     * Admin Routes
     * 
     *      super-admin
     *      building-manager
     *      admin
     *      general-staff
     */
    /***********************************************************************/
    
    
    /*
    |--------------------------------------------------------------------------
    | Data Sources
    |--------------------------------------------------------------------------
    |
    */
    Route::group(['prefix' => 'sources'], function() {
        Route::group(['namespace' => 'Admin'], function() {
            Route::get('/residents', 'UserController@getResidents')->middleware('role:super-admin|building-manager|admin');
            Route::get('/users', 'UserController@getUsers')->middleware('role:super-admin|admin');
            Route::get('/admins', 'UserController@getAdmins')->middleware('role:super-admin');

            Route::get('/buildings', 'BuildingController@getBuildings');
            Route::get('/items', 'BookableItemController@getBookableItems');

            Route::get('/calendar/{query?}', 'CalendarController@get')->middleware('role:super-admin|building-manager|admin|external');
            
            Route::get('/events/get-repeat-next-date', 'RecurringEventController@getRepeatNextDate');
        });
    });



    /*
    |--------------------------------------------------------------------------
    | API
    |--------------------------------------------------------------------------
    |
    */
    Route::prefix('api')->group(function () {
        Route::prefix('filepond')->group(function () {
            Route::post('/process', 'FilePondController@upload')->name('filepond.upload');
            Route::post('/delete', 'FilePondController@delete')->name('filepond.delete');
            Route::get('/load/{file_name}', 'FilePondController@get')->name('filepond.get');
        });
    });



    /*
    |--------------------------------------------------------------------------
    | Files
    |--------------------------------------------------------------------------
    |
    */
    Route::group(['prefix' => 'files', 'middleware' => 'role:super-admin|admin|building-manager'], function() {
        Route::post('/remove-gallery-image', 'FileController@removeGalleryImage');
        Route::post('/remove-pdf-attachment', 'FileController@removePDFAttachment');
        Route::post('/order-gallery-images', 'FileController@orderGalleryImages');
        Route::post('/order-pdf-terms', 'FileController@orderPDFTerms');
        Route::post('/move', 'FileController@move');
        Route::post('/rename', 'FileController@rename')->name('file.rename');
    });





    /*
    |--------------------------------------------------------------------------
    | Web App Routes: Front End
    |   - Views: Views/resident folder
    |--------------------------------------------------------------------------
    |
    */
    Route::group(['namespace' => 'Resident'], function() {

        // Dash
        Route::get('/', 'HomeController@index')->name('resident.index');

        // Global Search
        Route::get('/items-search', 'HomeController@search')->name('resident.search'); 
        
        // Admin building preview
        Route::get('/preview-building/{building_id}', 'HomeController@previewBuilding')->name('app.preview.building');
        Route::get('/switch-building/{building_id}', 'HomeController@switchBuilding')->name('app.switch.building');
        
        Route::get('/preview-item/{type}/{item_id}', 'HomeController@previewItem')->name('app.preview.item');
        Route::get('/back-to-admin', 'HomeController@backToAdmin')->name('app.backToAdmin');

        // Profile 
        Route::group(['prefix' => 'profile'], function() {
            Route::get('/', 'ProfileController@show')->name('resident.profile.show');
            Route::post('/store-contact-details', 'ProfileController@storeContact')->name('resident.profile.storeContact');
            Route::post('/store-notification-settings', 'ProfileController@storeUserSetting')->name('resident.profile.storeUserSetting');
            Route::post('/store-credit-card', 'ProfileController@storeCreditCard')->name('resident.profile.storeCreditCard');
            Route::post('/store-password', 'ProfileController@storePassword')->name('resident.profile.storePassword');
        });

        // Retail Deals
        Route::group(['prefix' => 'retail-deals'], function() {
            Route::get('/', 'DealsController@index')->name('resident.deals.index');
            Route::get('/history', 'DealsController@history')->name('resident.deals.history');
            Route::post('/redeem', 'DealsController@redeem')->name('resident.deal.redeem');
        });

        // 
        Route::get('/my-building', 'BuildingController@show')->name('resident.building.show');
        
        // My Bookings
        Route::get('/my-bookings', 'BookingController@index')->name('resident.bookings.index');
        
        Route::group(['prefix' => 'booking'], function() {
            Route::get('/{booking_id}', 'BookingController@get');
            Route::post('/{booking_id}', 'BookingController@update');
            Route::post('/{booking_id}/cancel', 'BookingController@cancel');
        });

        
        // My Building
        Route::get('/my-buildings', 'BuildingController@myBuilding')->name('resident.buildings.index');

        //  Bookable items
        Route::group(['prefix' => 'items'], function() {
            Route::get('/{type}/{item_id}', 'BookableItemController@show')->name('resident.item.show');
            // Create booking
            Route::post('/{type}/{item_id}', 'BookingController@create')->name('app.resident.booking.create');

            // Date validations (Room, Hire bookings)
            Route::post('/{type}/{item_id}/validate-date', 'BookableItemController@validateDate');
            Route::post('/{type}/{item_id}/get-dates-in-period', 'BookableItemController@getDatesInPeriod');
            
            Route::post('/{type}/{item_id}/get-cancellation-cut-off', 'BookableItemController@getCutOffDate');

            Route::post('/{type}/{item_id}/confirm-password', 'BookableItemController@confirmPassword')->name('app.resident.booking.confirmPassword');

            // Sync the Cart (for Services)
            Route::post('/{type}/{item_id}/sync-cart', 'CartController@sync');
        });


        Route::group(['prefix' => 'api'], function() {
            Route::post('/validate-booking-dates', 'BookableItemController@show');
            // get the list of bookings
            Route::any('/bookings', 'BookingController@list')->name('resident.api.booking.list');
            Route::any('/redeem-history', 'DealsController@list')->name('resident.api.redeems.list');
        });


    });



    /*
    |--------------------------------------------------------------------------
    | Web App Routes: Back End
    |   - Views: Views/app folder
    |--------------------------------------------------------------------------
    |
    */


    Route::group(['prefix' => 'admin', 'namespace' => 'Admin', 'middleware' => 'role:super-admin|admin|building-manager|general-staff|external'], function() {
        
        // Dash
        Route::get('/', 'HomeController@index')->name('app.index');

        // Profile 
        Route::group(['prefix' => 'profile'], function() {
            Route::get('/', 'ProfileController@show')->name('app.profile.show');
            Route::post('/store-contact-details', 'ProfileController@storeContact')->name('app.profile.storeContact');
            Route::post('/store-notification-settings', 'ProfileController@storeNotiSetting')->name('app.profile.storeNotiSetting');
            Route::post('/store-credit-card', 'ProfileController@storeCreditCard')->name('app.profile.storeCreditCard');
            Route::post('/store-password', 'ProfileController@storePassword')->name('app.profile.storePassword');
        });

        // Settings
        Route::group(['prefix' => 'settings', 'middleware' => 'role:super-admin|admin'], function() {
            Route::get('/', 'SettingController@index')->name('app.settings.index')->middleware('role:super-admin');
            
            Route::post('/categories', 'CategoryController@update')->name('app.settings.categories.update');    
            Route::post('/email-templates', 'SettingController@update')->name('app.settings.emailTemplates.update');    
        });

        // Users
        Route::any('users/{tab?}', 'UserController@index')->name('app.users.index'); 

        Route::group(['prefix' => 'user', 'middleware' => 'role:super-admin|building-manager'], function() {
            Route::any('list/{tab?}', 'UserController@listUsers')->name('app.user.list')->middleware('role:super-admin');
            Route::post('create', 'UserController@create')->name('app.user.create')->middleware('role:super-admin');
            Route::get('{user_id}', 'UserController@show')->name('app.user.show')->middleware('role:super-admin');
            Route::put('{user_id}/update', 'UserController@update')->name('app.user.update')->middleware('role:super-admin');
            Route::put('{user_id}/update-buildings', 'UserController@updateBuildings')->name('app.user.updateBuildings')->middleware('role:super-admin');
            Route::put('{user_id}/update-buildings', 'UserController@updateBuildings')->name('app.user.updateBuildings')->middleware('role:super-admin');
            Route::post('{user_id}/store-notification-settings', 'UserController@storeNotiSetting')->name('app.user.storeNotiSetting')->middleware('role:super-admin');

            // User - sub pages
            Route::delete('{user_id}/delete', 'UserController@destroy')->name('app.user.delete')->middleware('role:super-admin');
            Route::post('{user}/invite', 'UserController@invite')->name('app.user.invite')->middleware('role:super-admin|building-manager');
            Route::post('{user}/flag', 'UserController@flag')->name('app.user.flag')->middleware('role:super-admin');
        });

        // QR Code
        Route::any('qr-codes/{tab?}', 'QrCodeController@index')->name('app.qr-code.index'); 

        Route::group(['prefix' => 'qr-code', 'middleware' => 'role:super-admin|admin'], function() {
            Route::any('list/{tab?}', 'QrCodeController@listUsers')->name('app.qr-code.list');
            Route::post('create', 'QrCodeController@create')->name('app.qr-code.create')->middleware('role:super-admin');
            Route::get('{id}', 'QrCodeController@show')->name('app.qr-code.show');
            Route::put('{id}/update', 'QrCodeController@update')->name('app.qr-code.update')->middleware('role:super-admin');
            Route::delete('{id}/delete', 'QrCodeController@destroy')->name('app.qr-code.delete')->middleware('role:super-admin');
            Route::get('{id}/get-detail', 'QrCodeController@getDetail')->name('app.qr-code.api');
        });

        // Residents
        Route::get('residents/{tab?}', 'UserController@index')->name('app.residents.index'); 
        
        Route::group(['prefix' => 'resident', 'middleware' => 'role:super-admin|admin|building-manager|general-staff'], function() {
            Route::any('list/{tab?}', 'UserController@listResidents')->name('app.resident.list');
            Route::post('create', 'UserController@createResident')->name('app.resident.create');
            Route::post('import', 'UserController@createResidentFormFile')->name('app.resident.import');
            Route::get('{user_id}', 'UserController@show')->name('app.resident.show');
            Route::put('{user_id}/update', 'UserController@updateResident')->name('app.resident.update');
            // resident - sub pages
            Route::get('{user_id}/{tab?}', 'UserController@show')->name('app.resident.show.bookings');

            Route::post('{user_id}/store-notification-settings', 'UserController@storeUserSetting')->name('app.resident.profile.storeUserSetting');
        });

        // Marketing Communications
        Route::get('marketing-communications/{tab?}', 'MarketingCommunicationsController@index')->name('app.marketing-communications.index'); 
        Route::group(['prefix' => 'marketing-communication', 'middleware' => 'role:super-admin|admin'], function() {
            Route::any('list/{tab?}', 'MarketingCommunicationsController@list')->name('app.marketing-communications.list');
            Route::post('create', 'MarketingCommunicationsController@create')->name('app.marketing-communications.create');
            Route::post('import', 'MarketingCommunicationsController@createResidentFormFile')->name('app.marketing-communications.import');
            Route::get('get-resident-list', 'MarketingCommunicationsController@getResidentList')->name('app.marketing-communications.getResidentList');
            Route::get('{id}', 'MarketingCommunicationsController@show')->name('app.marketing-communications.show');
            Route::put('{id}/update', 'MarketingCommunicationsController@update')->name('app.marketing-communications.update');
            Route::delete('{id}/delete', 'MarketingCommunicationsController@destroy')->name('app.marketing-communications.delete');
        });

        // Media Managements
        Route::get('media-management', 'MediaManagementController@index')->name('app.media-management.index')->middleware('role:super-admin'); 

        // Buildings
        Route::get('buildings', 'BuildingController@index')->name('app.buildings.index');
       
        Route::group(['prefix' => 'building', 'middleware' => 'role:super-admin|building-manager'], function() {
            Route::any('list', 'BuildingController@list')->name('app.building.list');
            Route::post('create', 'BuildingController@store')->name('app.building.create')->middleware('role:super-admin|building-manager');
            Route::get('{building_id}', 'BuildingController@show')->name('app.building.show');
            Route::post('{building_id}/update', 'BuildingController@store')->name('app.building.update');

            Route::post('{building_id}/store-page', 'BuildingController@storePage')->name('app.building.store.content');

            Route::delete('{building_id}/delete', 'BuildingController@destroy')->name('app.building.delete');
        });


        // Bookable items
        Route::get('items/{tab?}', 'BookableItemController@index')->name('app.items.index');
 
        Route::group(['prefix' => 'item', 'middleware' => 'role:super-admin|admin|building-manager'], function() {
            
            Route::any('/list/{tab?}', 'BookableItemController@list')->name('app.item.list');
            
            Route::get('/{item_id}', 'BookableItemController@show')->name('app.item.show');
            Route::post('/store/{type}/{item_id?}', 'BookableItemController@store')->name('app.item.store');
            Route::delete('/{item_id}/delete', 'BookableItemController@destroy')->name('app.item.delete');
            
            Route::post('/clone/{item_id?}', 'BookableItemController@clone')->name('app.item.clone');
            Route::post('/publish/{item_id}', 'BookableItemController@publish');
            Route::post('/store-single/{item_id}', 'BookableItemController@storeSingle')->name('app.item.storeSingle');
            
            /* line items for services */
            Route::any('/{item_id}/service-items/list/', 'BookableItemController@lineItemList')->name('app.item.lineItems.list');

            Route::get('/service-items/get/{line_item_id}', 'BookableItemController@getLineItem');
            Route::post('/{item_id}/service-items/{line_item_id?}', 'BookableItemController@storeLineItem')->name('app.item.lineItems.store');
            Route::post('/{item_id}/service-items/{line_item_id}/clone', 'BookableItemController@cloneLineItem')->name('app.item.lineItems.clone');
            Route::delete('/{item_id}/service-items/{line_item_id}/delete', 'BookableItemController@deleteLineItem')->name('app.item.lineItems.delete');



        });

        // Retail Stores
        Route::get('retail-stores/{tab?}', 'RetailStoreController@index')->name('app.stores.index');
        
        Route::group(['prefix' => 'retail-store'], function() {
            Route::any('/list/{tab?}', 'RetailStoreController@list')->name('app.store.list');
            
            Route::post('/store/{store_id?}', 'RetailStoreController@store')->name('app.store.store');
            Route::get('/{store_id}/', 'RetailStoreController@show')->name('app.store.show');
            Route::delete('/delete/{store_id}', 'RetailStoreController@destroy')->name('app.store.delete');

            // Redeem history
            Route::get('/{store_id}/redeem-history', 'RetailStoreController@showRedeemHistory')->name('app.store.redeemHistory.show');
            Route::post('/{store_id}/redeem-history/list/', 'RetailDealController@listRedeemHistory')->name('app.store.redeemHistory.list');
        });


        // Retail Deals
        Route::get('retail-deals/{tab?}', 'RetailDealController@index')->name('app.deals.index');
        
        Route::group(['prefix' => 'retail-deal'], function() {
            Route::any('/list/{tab?}', 'RetailDealController@list')->name('app.deal.list');

            Route::get('/get/{deal_id}', 'RetailDealController@get')->name('app.deal.get');

            Route::post('/store/{store_id}/deal/{deal_id?}', 'RetailDealController@store')->name('app.deal.store');
            Route::post('/clone/{deal_id?}', 'RetailDealController@clone')->name('app.deal.clone');
            Route::delete('/delete/{deal_id}', 'RetailDealController@destroy')->name('app.deal.delete');
        });




        // Bookings
        Route::get('bookings/{tab?}', 'BookingController@index')->name('app.bookings.index');
       
        Route::group(['prefix' => 'booking', 'middleware' => 'role:super-admin|admin|building-manager|general-staff|external'], function() {
            Route::any('list/{tab?}', 'BookingController@list')->name('app.booking.list');
            Route::post('create', 'BookingController@store')->name('app.booking.create')->middleware('role:super-admin');
            Route::post('add-manual', 'BookingController@addManual')->name('app.booking.add-manual')->middleware('role:super-admin');
            Route::get('{booking_id}', 'BookingController@show')->name('app.booking.show');
            
            Route::get('{item_id}/get', 'BookingController@get')->name('app.booking.get');

            Route::put('{item_id}/update', 'BookingController@store')->name('app.booking.update')->middleware('role:super-admin');
            Route::delete('{item_id}/delete', 'BookingController@destroy')->name('app.booking.delete')->middleware('role:super-admin');
        });

        
        // Comments
        Route::group(['prefix' => 'comments'], function() {
            Route::post('/store', 'CommentController@storeComment');
            Route::post('/delete', 'CommentController@deleteComment');
            Route::get('/get/{comment_id}', 'CommentController@getComment');
        });


        // Payment Receipts / Transactions
        Route::group(['prefix' => 'transactions'], function() {
            Route::post('/refund/{transaction_id}', 'TransactionController@refund')->name('app.transaction.refund');
            Route::post('/release-bond/{transaction_id}/', 'TransactionController@releaseBond')->name('app.transaction.releaseBond');
            Route::post('/retry/{transaction_id}/', 'TransactionController@retryPayment')->name('app.transaction.retry');
        });



        // Settings
        Route::group(['prefix' => 'settings'], function() {
           
            // Email templates
            Route::post('/store-template', 'SettingController@storeEmailTemplate')->name('app.settings.update');

            // Settings / Categories
            Route::group(['prefix' => 'category'], function() {

                Route::any('/list', 'SettingController@list')->name('app.settings.category.list');

                Route::get('/get/{category_id?}', 'SettingController@getCategory')->name('app.settings.category.get');
                Route::post('/store/{category_id?}', 'SettingController@storeCategory')->name('app.settings.category.store');
                Route::post('/store-order/{category_id?}', 'SettingController@storeCategoryOrder')->name('app.settings.category.storeOrder');
                Route::delete('/delete/{category_id}', 'SettingController@deleteCategory')->name('app.settings.category.delete');
            });

        });




    });  


    /*
    |--------------------------------------------------------------------------
    | Web App Routes: TEST ROUTE
    |--------------------------------------------------------------------------
    |
    */


  
