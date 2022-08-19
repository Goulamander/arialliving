<h4>Payment</h4>
<div class="booking-sum pt-0">
    @php $hired_item = json_decode($booking->line_items); @endphp
    <ul>
        <li><strong class="_qty">{{$booking->qty}}</strong> x <b>{{$booking->bookableItem->title}}</b> <span>{{priceFormat($hired_item->price)}}<small>/{{$hired_item->price_unit}}</small></span></li>
        <li>Booking total (inc. GST) <span class="_subtotal">{{priceFormat($booking->subtotal)}}</span></li>
        <li>Security deposit <span>{{priceFormat($booking->bond)}}</span></li>
    </ul>
    <h4 class="mt-0">@if($booking->status == \App\Models\Booking::$STATUS_ACTIVE)Charge @else Charged @endif amount: <span class="_total">{{priceFormat($booking->total + $booking->bond)}}</span></h4>
</div>