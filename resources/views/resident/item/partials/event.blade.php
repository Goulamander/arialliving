<div class="booking-step text-left">

    <div class="booking-event">
        <div class="booking-summary">
            <strong>{{Auth::user()->first_name}} {{Auth::user()->last_name}}</strong>

            <div class="row">
                <div class="col-6">
                    Event Date &amp; time
                    @if($item->event->event_type == \App\Models\BookableItem\BookableItemEvent::$TYPE_SINGLE)
                    <span class="_date">{{dateFormat($item->event->event_date)}}</span> 
                    @else
                    <span class="_date">{{dateFormat($item->recurring->repeat_next)}}</span>
                    @endif
                    <span class="_time">@if($item->event->event_from) {{bookingTime($item->event->event_from, $item->event->event_to)}} @else All day @endif</span>
                </div>
                <div class="col-6">
                    Location
                    @if($item->event->location)
                    <span class="_location">{{$item->event->location->title}}</span>
                    @else
                    <span class="_location">{{$item->event->location_name}}</span>
                    @endif
                </div>
            </div>

        </div>

        
        @if(!$item->is_free && !Auth::user()->isResidentVip() && !$item->isFreeAsAdmin())
        <div class="booking-summary-detail text-left">
            <ul class="line-items">
                @if($item->admin_fee)
                <li @if($item->event->allow_guests > 0) class="text-right" @endif>
                    @if($item->event->allow_guests > 0)
                        <div class="_attendees">
                            <input type="number" class="form-control" name="attendees_num" placeholder="Num of attendees" value="1" min="1" max="{{$item->event->allow_guests + 1}}" data-parsley-error-message="At least 1, and maximum {{$item->event->allow_guests + 1}} attendees" required>
                            <input type="hidden" name="admin_fee" value="{{$item->admin_fee}}">
                            <label class="control-label">attendee</label>
                            <small>(Max {{$item->event->allow_guests + 1}})</small>
                        </div>
                    @endif    
                Admin fee <span><i class="icon-info" data-tippy-content="{{__('messages.admin_fee')}}"></i> {{priceFormat($item->admin_fee)}}</span></li>
                @endif
            </ul>
            <h4>Your total: <span class="_total">{{priceFormat($item->admin_fee)}}</span></h4>
            {{-- Credit Card --}}
            @include('resident.item.partials._creditCard')
        </div>
        @elseif($item->event->allow_guests > 0)
        <div class="booking-summary-detail text-left">
            <ul class="line-items">
                <li>
                    <div class="_attendees">
                        <input type="number" class="form-control" name="attendees_num" placeholder="Num of attendees" value="1" min="1" max="{{$item->event->allow_guests + 1}}" required>
                        <label class="control-label">attendee</label>
                        <small>(Max {{$item->event->allow_guests + 1}})</small>
                    </div>
                </li>
            </ul>
        </div>
        @endif


    </div>

    @include('resident.item.partials._comment', ['label' => 'Booking'])
    @include('resident.item.partials._terms')
    @include('resident.item.partials._submit')
</div>