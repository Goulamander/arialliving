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
                $item_price = isset($cart_item->item->price) ? $cart_item->item->price : 0;
                $price_condition = $item_price > 0 && !Auth::user()->isResidentVip() && !$item->isFreeAsAdmin();
                @endphp
                <div data-id="{{$cart_item->id}}" data-price="{{$price_condition ? $item_price : 0}}" class="_item">
                    @if(isset($cart_item->item->is_thumb) && $cart_item->item->is_thumb == true)
                    <img class="thumb" src="{{$item->getLineItemThumb($cart_item->id)}}" alt="{{$cart_item->item ? $cart_item->item->name : ''}}"/>
                    @endif
                    <div class="item_body">
                        <span class="item_name">{{$cart_item->item ? $cart_item->item->name : ''}}</span>
                        <span class="item_price">{{$price_condition ? priceFormat($item_price) : 'Free'}}</span>
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
        if($item->service->bond_amount && $item->service->bond_amount > 0) {
            $total = $subtotal + $item->service->bond_amount;
        }
    }

    $cart_empty = !$item->cart || ($item->cart && !json_decode($item->cart->items, true)) ? true : false;

    @endphp
    
    <div class="cart-sum @if($cart_empty) hidden @endif">
        <span>Subtotal <span class="cart_subtotal" data-price="{{$subtotal}}">{{priceFormat($subtotal)}}</span></span>
        @if($item->admin_fee)
            <span>Admin fee <span class="cart_admin_fee" data-price="{{$item->admin_fee}}">{{priceFormat($item->admin_fee)}}</span></span>
        @endif
        @if($item->service->bond_amount && $item->service->bond_amount > 0)
            <span>Security deposit <i class="icon-info" data-tippy-content="{{__('messages.deposit_info')}}"></i> <span class="cart_admin_bond" data-price="{{$item->service->bond_amount}}">{{priceFormat($item->service->bond_amount)}}</span></span>
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
        <input type="hidden" name="_bond" value="{{$item->service->bond_amount ?? 0}}"/>

        <ul class="line-items">
            <li>Subtotal (inc. GST) <span class="_subtotal"></span></li>
            @if($item->admin_fee)
            <li>Admin fee <span><i class="icon-info" data-tippy-content="{{__('messages.admin_fee')}}"></i> {{priceFormat($item->admin_fee)}}</span></li>
            @endif
        </ul>
        <ul>
            @if($item->service->bond_amount && $item->service->bond_amount > 0)
            <li>Security deposit <span><i class="icon-info" data-tippy-content="{{__('messages.deposit_info')}}"></i> {{priceFormat($item->service->bond_amount)}}</span></li>
            @endif
        </ul>

        <h4>Your total: <span class="_total"></span></h4>
        @if((!Auth::user()->isResidentVip() && $item->isPaymentToAria()) || $item->service->bond_amount)
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
