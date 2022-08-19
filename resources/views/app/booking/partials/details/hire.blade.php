
<h4>Hire details</h4>

{{-- Date/Time --}}
<div class="row">
    <div class="col-12">
        <small class="text-muted">Booking Dates</small>
        <p><h5><strong>{{bookingDate($booking->start, $booking->end)}}</strong></h5></p>
    </div>
</div>
<div class="row">
    <div class="col-4">
        <small>Pickup</small>
        <p>{{timeFormat($booking->start)}}</p>
    </div>
    <div class="col-4">
        <small>Drop-off</small>
        <p>{{timeFormat($booking->end)}}</p>
    </div>
    <div class="col-4">
        <small>Length</small>
        <p>{{$booking->length_str}}</p>
    </div>
</div>