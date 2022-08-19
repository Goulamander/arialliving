{{-- Step 1 --}}
<div class="booking-step"> 
    
    <div class="cart-items">
    @if($item->cart)
        @php $cart_items = json_decode($item->cart->items) @endphp
        @if($cart_items)
            @foreach($cart_items as $cart_item)
                @php
                // attach the line item to the cart item
                $cart_item->item = $item->line_items->first(function($it) use($cart_item) {
                    return $it->id == $cart_item->id;
                });
                $price_condition = $cart_item->item->price > 0 && !Auth::user()->isResidentVip() && !$item->isFreeAsAdmin();
                @endphp
                <div data-id="{{$cart_item->id}}" data-price="{{$price_condition ? $cart_item->item->price : 0}}" class="_item">
                    @if($cart_item->item->is_thumb == true)
                    <img class="thumb" src="{{$item->getLineItemThumb($cart_item->id)}}" alt="{{$cart_item->item ? $cart_item->item->name : ''}}"/>
                    @endif
                    <div class="item_body">
                        <span class="item_name">{{$cart_item->item->name}}</span>
                        <span class="item_price">{{$price_condition ? priceFormat($cart_item->item->price) : 'Free'}}</span>
                    </div>
                    <div class="item_controls">
                        <button type="button" class="_minus btn btn-sm">-</button>
                        <input type="number" class="cart_qty" value="{{$cart_item->qty}}" min="1" max="100"/>
                        <button type="button" class="_add btn btn-sm">+</button>
                        <button type="button" class="_remove no-btn"><i class="icon-close"></i></button>
                    </div>
                </div>
            @endforeach

        @else
        <div class="cart_empty_sate">
            <h4>Add items to you cart</h4>
        </div>   
        @endif
    @else
        <div class="cart_empty_sate">
            <h4>Add items to you cart</h4>
        </div>
    @endif
    </div>
    @php

    $subtotal = 0;
       $total = 0;

    if($item->cart && !Auth::user()->isResidentVip()) {
        $subtotal = $item->calculateSubTotal($cart_items);
        $total = $subtotal;

        if($item->service && $item->admin_fee) {
            $total = $subtotal + $item->admin_fee;
        }
    }

    $cart_empty = !$item->cart || ($item->cart && !json_decode($item->cart->items, true)) ? true : false;

    @endphp
    
    <div class="cart-sum @if($cart_empty) hidden @endif">
        <span>Subtotal <span class="cart_subtotal" data-price="{{$subtotal}}">{{priceFormat($subtotal)}}</span></span>
        @if($item->admin_fee)
            <span>Admin fee <span class="cart_admin_fee" data-price="{{$item->admin_fee}}">{{priceFormat($item->admin_fee)}}</span></span>
        @endif
        <h4>Total <small>inc. GST</small> <span class="cart_total" data-price="{{$total}}">{{priceFormat($total)}}</span></h4>
    </div>
    @if($item->service->is_date)
    <button type="button" name="continue_order" data-page="1" class="btn btn-primary mt-4 btn-lg btn-round btn-submit" @if($cart_empty) disabled @endif>Continue <b>Order</b><i class="material-icons">arrow_forward</i></button>
    @else
    <button type="button" name="continue_order" data-page="1" data-summary="true" class="btn btn-primary mt-4 btn-lg btn-round btn-submit" @if($cart_empty) disabled @endif>Continue <b>Order</b><i class="material-icons">arrow_forward</i></button>
    @endif
</div>

@if($item->service->is_date)
{{-- Step 2 --}}
<div class="booking-step">
    <button type="button" class="booking__go_back btn btn-primary btn-simple btn-sm btn-round pull-right" data-page="0" data-enable-cart-buttons="true"><i class="material-icons">arrow_backward</i> Back to items</button>
    {{-- Service dates--}}
    <h3><span>{{$item->service->date_field_name}}</span></h3>
    @include('resident.item.partials._calendar', ['item_type' => 'service'])
    @if (in_array($item->service->is_date, [App\Models\BookableItem\BookableItemService::$IS_DATE_ADD_DATE_AND_TIME, App\Models\BookableItem\BookableItemService::$IS_DATE_ADD_DATE_AND_TIME_RESTRICTED]))
        <div id="bookingDetailsServiceTab" class="collapse">
            <h3 class="c-line"><span>Your booking details</span></h3>
            <div class="date">
                <span id="booking_date_service">-</span>
            </div>
            <div class="row">
                <div class="col-4">
                    <small>From</small>
                    <select class="form-control" id="booking_from" name="time_start" class="booking_from" required>
                        {{-- <option value="">From</option> --}}
                        @foreach (App\Models\BookableItem\BookableItemService::$HOURS_ARR as $key => $hour)
                            @if (!($item->service->is_date == App\Models\BookableItem\BookableItemService::$IS_DATE_ADD_DATE_AND_TIME_RESTRICTED && ($key < $item->service->timeslot_from || $key > $item->service->timeslot_to)))
                                <option value="{{$hour}}">{{App\Models\BookableItem\BookableItemService::$HOURS_24h_ARR[$key]}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-4">
                    <small>To</small>
                    <select class="form-control" id="booking_to" name="time_end" class="booking_to" required>
                        {{-- <option value="">To</option> --}}
                        @foreach (App\Models\BookableItem\BookableItemService::$HOURS_ARR as $key => $hour)
                            @if (!($item->service->is_date == App\Models\BookableItem\BookableItemService::$IS_DATE_ADD_DATE_AND_TIME_RESTRICTED && ($key < $item->service->timeslot_from || $key > $item->service->timeslot_to)))
                                <option value="{{$hour}}">{{App\Models\BookableItem\BookableItemService::$HOURS_24h_ARR[$key]}}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-4">
                    <small>Length</small>
                    <span id="booking_length" class="booking_length mt-2">-</span>
                    <input type="hidden" name="booking_length"/>
                    <input type="hidden" name="booking_length_unit"/>
                </div>
            </div>
        </div>
    @endif
    <button type="button" name="continue_order" data-page="2" data-summary="true" class="btn btn-primary mt-4 btn-lg btn-round btn-submit" disabled>Complete <b>Order</b><i class="material-icons">arrow_forward</i></button>
</div>
@endif

{{-- Step 3 --}}
<div class="booking-step text-left">
    @if($item->service->is_date)    
    <button type="button" class="booking__go_back btn btn-primary btn-simple btn-sm btn-round pull-right" data-page="1"><i class="material-icons">arrow_backward</i> Back to calendar</button>
    @else
    <button type="button" class="booking__go_back btn btn-primary btn-simple btn-sm btn-round pull-right" data-page="0" data-enable-cart-buttons="true"><i class="material-icons">arrow_backward</i> Back to items</button>
    @endif
    
    {{-- Booking summary --}}
    <div class="booking-summary">
        <strong>{{Auth::user()->first_name}} {{Auth::user()->last_name}}</strong>
        @if($item->service->is_date)
            {{$item->service->date_field_name}}
            <span class="_date"></span>
        @endif
    </div>

    <div id="booking_service_costing" class="booking-summary-detail text-left">

        <ul class="line-items">
            <li>Subtotal (inc. GST) <span class="_subtotal"></span></li>
            @if($item->admin_fee)
            <li>Admin fee <span><i class="icon-info" data-tippy-content="{{__('messages.admin_fee')}}"></i> {{priceFormat($item->admin_fee)}}</span></li>
            @endif
        </ul>

        <h4>Your total: <span class="_total"></span></h4>
        @if(!Auth::user()->isResidentVip())
            {{-- Credit Card --}}
            @include('resident.item.partials._creditCard')
        @endif
    </div>  


    @include('resident.item.partials._comment', ['label' => 'Order'])
    @include('resident.item.partials._terms')

    <div class="booking-confirmation">
        <button type="submit" name="submit_booking" class="btn btn-primary mt-4 btn-lg btn-round btn-submit">Submit <b>Order</b></button>
    </div>

</div>