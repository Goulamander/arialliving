<h4>Booking Fee</h4>
<div class="booking-sum pt-0">
    <ul>
        <li>Booking fee (inc. GST) <span class="_subtotal">{{priceFormat($booking->admin_fee)}}</span></li>
        @if ($booking->other_fee && count($booking->other_fee) > 0)
            @foreach ($booking->other_fee as $fee)
                <li>Cleaning fee ({{$fee['name']}}) <span class="_subtotal">{{priceFormat($fee['fee'])}}</span></li>
            @endforeach
        @endif
        @if($booking->bond && $booking->bond > 0)
        <li>Security deposit <span>{{priceFormat($booking->bond)}}</span></li>
        @endif
    </ul>    
    <h4 class="mt-0">@if($booking->status == \App\Models\Booking::$STATUS_ACTIVE)Charge @else Charged @endif amount: <span class="_total">{{priceFormat($booking->total + $booking->bond)}}</span></h4>
</div>
