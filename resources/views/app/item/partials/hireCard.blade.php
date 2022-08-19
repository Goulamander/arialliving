<div class="row">
    <div class="col-6">
        <small class="text-muted">Item Visibility</small>
        <p>{{$item->getVisibility()}}</p>
    </div>
</div>

<h4>Stock Options</h4>
<div class="row">
    <div class="col-4">
        <small class="text-muted">Available stock</small>
        <p>{{$item->hire->available_qty}}</p>
    </div>
    <div class="col-4">
        <small class="text-muted">Allow multiple QTY</small>
        <p>{{$item->hire->allow_multiple ? 'Yes' : 'No'}}</p>
    </div>
    <div class="col-4">
        <small class="text-muted">Maximum limit</small>
        <p>{{$item->hire->allow_multiple_max ?? '-'}}</p>
    </div>
</div>

<h4>Item Pricing</h4>
<div class="row">
    <div class="col-4">
        <small class="text-muted">Price of Hire</small>
        <p>{{ $item->hire->item_price && $item->hire->item_price > 0 ? priceFormat($item->hire->item_price) : 'Free' }}</p>
    </div>
    <div class="col-4">
        <small class="text-muted">Price Unit</small>
        <p>/{{$item->hire->item_price_unit}}</p>
    </div>
    <div class="col-4">
        <small class="text-muted">Bond Amount</small>
        <p>{{$item->hire->bond_amount ? priceFormat($item->hire->bond_amount) : '-'}}</p>
    </div>
</div>

<h4>Booking options</h4>
<div class="row">
    <div class="col-4">
        <small class="text-muted">Min / Max length of booking</small>
        <p>{{$item->hire->booking_min_length}}<small>{{str_plural('hr', $item->hire->booking_min_length)}}</small> / {{$item->hire->booking_max_length}}<small>{{str_plural('hr', $item->hire->booking_max_length)}}</small></p>
    </div>

    <div class="col-4">
        <small class="text-muted">Allow multiday booking</small>
        <p>{{$item->hire->allow_multiday ? 'Allow' : 'Don\'t Allow'}}</p>
    </div>
    <div class="col-4">
        <small class="text-muted">Gap between booking</small>
        <p>{{$item->hire->booking_gap}} <small>min</small></p>
    </div>
</div>


<h4>Booking policy</h4>
<div class="row mb-3">
    <div class="col-12">
        <span>Residents can make bookings up to <b>{{$item->prior_to_book_hours}}{{str_plural('hr', $item->prior_to_book_hours)}}</b> before start time.</span>   
    </div>
    <div class="col-12">
        <span>Bookings can be changed or cancelled, up to <b>{{$item->cancellation_cut_off}}{{str_plural('hr', $item->cancellation_cut_off)}}</b> before start.</span>
    </div>
</div>   