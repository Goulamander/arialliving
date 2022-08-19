
<div class="row">
    <div class="col-6">
        <small class="text-muted">Item Visibility</small>
        <p>{{$item->getVisibility()}}</p>
    </div>
    {{-- <div class="col-6">
        <small class="text-muted">Booking Fee</small>
        <p>{{ $item->admin_fee && $item->admin_fee > 0 ? priceFormat($item->admin_fee) : 'Free' }}</p>
    </div> --}}
</div>

<div>
    <h4>Booking Fee</h4>
    <div class="row">
        <div class="col-12">
            <small class="text-muted">Admin Fee</small>
            <p>{{ $item->admin_fee && $item->admin_fee > 0 ? priceFormat($item->admin_fee) : 'Free' }}</p>
        </div>
        @if ($item->bookableItemFees()->exists())
            <div class="col-12 mt-3">
                <small class="text-muted">Cleaning Fee</small>
                @foreach ($item->bookableItemFees()->get() as $fee)
                    <div class="row">
                        <div class="col">{{$fee->name}}</div>
                        <p class="col">{{priceFormat($fee->fee)}}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<h4>Booking Options</h4>

<div class="row">
    <div class="col-4">
        <small class="text-muted">Daily limit</small>
        <p>{{$item->room->daily_booking_limit ?? '-'}}</p>
    </div>
    <div class="col-4">
        <small class="text-muted">Available from</small>
        <p>{{timeFormat($item->room->booking_from_time)}}</p>
    </div>
    <div class="col-4">
        <small class="text-muted">Available  to</small>
        <p>{{timeFormat($item->room->booking_to_time)}}</p>
    </div>
</div>
<hr>
<div class="row">
    <div class="col-4">
        <small class="text-muted">Min. length</small>
        <p>{{$item->room->booking_min_length}} <small>{{str_plural('hr', $item->room->booking_min_length)}}</small></p>
    </div>
    <div class="col-4">
        <small class="text-muted">Max. length</small>
        <p>{{$item->room->booking_max_length}} <small>{{str_plural('hr', $item->room->booking_max_length)}}</small></p>
    </div>
    <div class="col-4">
        <small class="text-muted">Booking Gap</small>
        <p>{{$item->room->booking_gap}} <small>min</small></p>
    </div>
</div>
<hr>
<div class="row">

    <div class="col-8">
        <small class="text-muted">Multiday bookings are allowed?</small>
        <p>{{$item->room->allow_multiday ? 'Yes' : 'No'}}</p>
    </div>
</div>    
<hr>
<div class="row">

    <div class="col-8">
        <small class="text-muted">Maximum number of bookings per day</small>
        <p>{{$item->room->maximum_number_of_bookings_per_day ?? 'No'}}</p>
    </div>
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

