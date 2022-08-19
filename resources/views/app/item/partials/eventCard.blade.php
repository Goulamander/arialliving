<div class="row">
    <div class="col-6">
        <small class="text-muted">Item Visibility</small>
        <p>{{$item->getVisibility()}}</p>
    </div>
    <div class="col-6">
        <small class="text-muted">Admin Fee</small>
        <p>{{ $item->admin_fee && $item->admin_fee > 0 ? priceFormat($item->admin_fee) : 'Free' }}</p>
        
    </div>
</div>

<h4>Location &amp; Event Type</h4>

<div class="row">
    <div class="col-6">
        <small class="text-muted">Location</small>
        @if($item->event->location_id)
        <p><a href="{{route('app.item.show', $item->event->location_id)}}" target="_blank" class="link">{{$item->event->location->title ?? '-'}}</a></p>
        @else
        <p>{{$item->event->location_name ?? '-'}}</p>
        @endif
    </div>
    <div class="col-6">
        <small class="text-muted">Event type</small>
        <p>{{$item->event->eventType()}}</p>
    </div>
</div>

<h4>Event Dates</h4>
@if($item->event->event_type == 1)
    <div class="row">
        <div class="col-6">
            <small class="text-muted">Date</small>
            <p>{{ $item->event->event_date ? dateFormat($item->event->event_date) : '-' }}</p>
        </div>
        <div class="col-6">
            <small class="text-muted">From/To</small>
            @if($item->event->event_from)
                <p>{{timeFormat($item->event->event_from)}} / {{timeFormat($item->event->event_to)}}</p>
            @else
                <p>All day event</p>
            @endif
        </div>
    </div>
@else
    <div class="row">
        <div class="col-6">
            <small class="text-muted">Recurring Frequency</small>
            <p>{{$item->recurring->getRecurringFrequency()}}</p>
        </div>
        <div class="col-6">
            <small class="text-muted">Next Date / Time</small>
            @if($item->event->event_from)
            <p>{{dateFormat($item->recurring->repeat_next)}}<br><small>{{timeFormat($item->event->event_from)}} - {{timeFormat($item->event->event_to)}}</small></p>
            @else
            <p>{{dateFormat($item->recurring->repeat_next)}}<br><small>All day event</small></p> 
            @endif
        </div>
    </div>
@endif

<h4>Attendees</h4>
<div class="row">
    <div class="col-6">
        <small class="text-muted">Max. number of attendees</small>
        <p>{{$item->event->attendees_limit ?? 'Unlimited'}}</p>
    </div>
    <div class="col-6">
        <small class="text-muted">Allowed guests per attendee</small>
        <p>{{$item->event->allow_guests}}</p>
    </div>
    <!-- <div class="col-12">
        <small class="text-muted">Enable RSVP</small>
        <p>{{$item->event->is_rsvp ? 'Yes' : 'No'}}</p>
    </div> -->
</div>

<h4>Booking policy</h4>
<div class="row mb-3">
    <div class="col-12">
        <small>Residents can make bookings up to <b>{{$item->prior_to_book_hours}}{{str_plural('hr', $item->prior_to_book_hours)}}</b> before start time.</small>   
    </div>
    <div class="col-12">
        <small>Bookings can be changed or cancelled, up to <b>{{$item->cancellation_cut_off}}{{str_plural('hr', $item->cancellation_cut_off)}}</b> before start.</small>
    </div>
</div>

 



