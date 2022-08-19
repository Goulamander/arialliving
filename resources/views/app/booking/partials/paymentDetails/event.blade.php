<h4>Admin fee</h4>
<div class="booking-sum pt-0">
    <ul>
        <li class="p-0"><strong class="_qty">{{$booking->event->attendees_num}} {{str_plural('attendee', $booking->event->attendees_num)}} x</strong> <span>Admin Fee {{priceFormat($booking->admin_fee)}}</span></li>
    </ul>
    <h4 class="mt-0">@if($booking->status == \App\Models\Booking::$STATUS_ACTIVE)Charge @else Charged @endif amount: <span class="_total">{{priceFormat($booking->total)}}</span></h4>
</div>