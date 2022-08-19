
<h4>Event details</h4>

{{-- Date/Time --}}
<div class="row">
    <div class="col-6">
        <small class="text-muted">Event Date</small>
        <p><h5><strong>{{$booking->showEventDate()}}</strong></h5></p>
    </div>
    <div class="col-6">
        <small class="text-muted">Location</small>
        @if($booking->bookableItem->event->location)
        <p><a href="{{route('app.item.show', $booking->bookableItem->event->location->id)}}" class="arrow" target="_blank">{{$booking->bookableItem->event->location->title}}</a></p>
        @else
        <p>{{$booking->bookableItem->event->location_name}}</p>
        @endif
    </div>
</div>
<div class="row">
    <div class="col-4">
        <small>From</small>
        {{-- <p>{{$booking->start ? timeFormat($booking->start) : 'All day'}}</p> --}}
        <p>{{$booking->bookableItem->event->event_from ? timeFormat($booking->bookableItem->event->event_from) : 'All day'}}</p>
    </div>
    <div class="col-4">
        <small>To</small>
        {{-- <p>{{$booking->end ? timeFormat($booking->end) : '-'}}</p> --}}
        <p>{{$booking->bookableItem->event->event_to ? timeFormat($booking->bookableItem->event->event_to) : '-'}}</p>
    </div>
    <div class="col-4">
        <small>Length</small>
        {{-- <p>{{$booking->length_str ? $booking->length_str : '-'}}</p> --}}
        <p>{{timeLength($booking->bookableItem->event->event_from, $booking->bookableItem->event->event_to)}}</p>
    </div>
</div>