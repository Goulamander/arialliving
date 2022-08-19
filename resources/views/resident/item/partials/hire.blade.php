{{-- Step 1 --}}
<div class="booking-step">  
    {{-- Booking Calendar --}}
    @include('resident.item.partials._calendar', ['item_type' => 'hire'])
    <button type="button" name="complete_booking" class="btn btn-primary mt-4 btn-lg btn-round btn-submit" disabled>Complete <b>Booking</b><i class="material-icons">arrow_forward</i></button>
</div>

{{-- Step 2 --}}
<div class="booking-step text-left">
    <button type="button" class="booking__go_back btn btn-primary btn-simple btn-sm btn-round pull-right"><i class="material-icons">arrow_backward</i> Back to calendar</button>
   
    {{-- Booking summary --}}
    <div class="booking-summary">
        <strong>{{Auth::user()->first_name}} {{Auth::user()->last_name}}</strong>
        <span class="_date"></span>
        <span class="_time"></span>
    </div>

    {{-- Get Hire fields --}}
    @if($item->is_free == false && !Auth::user()->isResidentVip() && !$item->isFreeAsAdmin())
    <div id="booking_hire_costing" class="booking-summary-detail text-left">
        @if($item->hire->allow_multiple) @endif
        <input type="hidden" name="_available_qty" value="{{$item->hire->available_qty}}"/>
        <input type="hidden" name="_qty" value=""/>
        <input type="hidden" name="_price" value="{{$item->hire->item_price}}"/>
        <input type="hidden" name="_price_unit" value="{{$item->hire->item_price_unit}}"/>
        <input type="hidden" name="_subtotal" value=""/>
        <input type="hidden" name="_bond" value="{{$item->hire->bond_amount}}"/>
        <ul>
            <li><strong class="_qty">1</strong> x <b>{{$item->title}}</b> <span>{{priceFormat($item->hire->item_price)}}<small>/{{$item->hire->item_price_unit}}</small></span></li>
            <li>Length of booking <span class="_length"></span></li>
            <li>Your booking total (inc. GST) <span class="_subtotal"></span></li>
            @if($item->hire->bond_amount)
            <li>Security deposit <span><i class="icon-info" data-tippy-content="{{__('messages.deposit_info')}}"></i> {{priceFormat($item->hire->bond_amount)}}</span></li>
            @endif
        </ul>
        <h4>Charge amount: <span class="_total"></span></h4>
        {{-- Credit Card --}}
        <div class="payment_notes">
            <span>Your payment will be charged on <b class="payment_date">__</b></span><br>
            <small>{{__('messages.booking.payment_note_sub')}}</small>
        </div>
        @include('resident.item.partials._creditCard')
    </div>    
    @endif

    @include('resident.item.partials._comment', ['label' => 'Booking'])
    @include('resident.item.partials._terms')
    @include('resident.item.partials._submit')
</div>