@if($booking->start)
{{-- Date/Time --}}
<div class="row">
    <div class="col-12">
        <small class="text-muted">{{$booking->bookableItem->service->date_field_name}}</small>
        <p><h5><strong>{{bookingDate($booking->start, $booking->end)}}</strong></h5></p>
    </div>
</div>
@endif