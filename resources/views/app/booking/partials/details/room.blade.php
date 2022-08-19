
<h4>Booking details</h4>

{{-- Date/Time --}}
<div class="row">
    <div class="col-12">
        <small class="text-muted">Booking Dates</small>
        <p><h5><strong>{{bookingDate($booking->start, $booking->end)}}</strong></h5></p>
    </div>
</div>
<div class="row pb-2">
    <div class="col-4">
        <small>From</small>
        <p>{{timeFormat($booking->start)}}</p>
    </div>
    <div class="col-4">
        <small>To</small>
        <p>{{timeFormat($booking->end)}}</p>
    </div>
    <div class="col-4">
        <small>Length</small>
        <p>{{$booking->length_str}}</p>
    </div>
</div>
<hr>
<div class="row mt-4">
    <div class="col-8">
        <small class="text-muted">Cleaning Required</small>
        <p>{{$booking->cleaning_label}}</p>
    </div>
</div>
