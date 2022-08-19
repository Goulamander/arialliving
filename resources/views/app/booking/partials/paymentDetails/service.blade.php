<div class="booking-sum pt-0">
    <ul>
        @if($booking->line_items)
            @foreach($booking->line_items as $item)
                <li><strong class="_qty">{{$item->qty}}</strong> x <b>{{$item->name}}</b> <span>{{priceFormat($item->price)}}</span></li>
            @endforeach
        @endif
    </ul>
    <h4 class="mt-0">@if($booking->status == \App\Models\Booking::$STATUS_ACTIVE)Charge @else Charged @endif amount: <span class="_total">{{priceFormat($booking->total)}}</span></h4>
</div>