{{-- Step 1 --}}
<div class="booking-step">  
    {{-- Booking Calendar --}}
    @include('resident.item.partials._calendar', ['item_type' => 'room'])
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

    @if(!$item->is_free && !Auth::user()->isResidentVip() && !$item->isFreeAsAdmin())
    <div class="booking-summary-detail text-left">
        <ul class="line-items">
            @if($item->admin_fee && $item->admin_fee > 0)
            <li>Admin fee <span><i class="icon-info" data-tippy-content="{{__('messages.admin_fee')}}"></i> {{priceFormat($item->admin_fee)}}</span></li>
            @endif

            @if($item->bookableItemFees()->exists())
            <li id="_cleaning_fee"></li>
            @endif
        </ul>
        <h4>Your total: <span class="_total" data-total="{{$item->admin_fee}}">{{priceFormat($item->admin_fee)}}</span></h4>
        {{-- Credit Card --}}
        @include('resident.item.partials._creditCard')
    </div>
    @endif

    @include('resident.item.partials._comment', ['label' => 'Booking'])
    @include('resident.item.partials._terms')
    @include('resident.item.partials._submit')
</div>