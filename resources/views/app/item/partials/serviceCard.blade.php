
<div class="row">
    <div class="col-6">
        <small class="text-muted">Item Visibility</small>
        <p>{{$item->getVisibility()}}</p>
    </div>
</div>

<h4>Service Date &amp; Time Settings</h4>
<div class="row">
    <div class="col-6 pr-0">
        <small class="text-muted">Date Picker</small>
        {{-- <p>{{$item->service->is_date ? 'Enable' : 'Disable'}}</p> --}}
        <p>
            @if ($item->service && $item->service->is_date)
                @switch($item->service->is_date)
                    @case(App\Models\BookableItem\BookableItemService::$IS_DATE_ADD_DATE_ONLY)
                        Add date only (unrestricted)
                        @break
                    @case(App\Models\BookableItem\BookableItemService::$IS_DATE_ADD_DATE_AND_TIME)
                        Add date and time (unrestricted)
                        @break
                    @case(App\Models\BookableItem\BookableItemService::$IS_DATE_ADD_DATE_AND_TIME_RESTRICTED)
                    Add date and time (restricted)
                        @break
                    @case(App\Models\BookableItem\BookableItemService::$IS_DATE_ADD_DATE_RESTRICTED)
                    Add date(restricted)
                    @break
                    @case(App\Models\BookableItem\BookableItemService::$IS_DATE_ADD_TIMESLOT)
                        Add timeslot selection
                        <div>Session Length ({{$item->service->session_length}} hours)</div>
                        <div>Booking Gap Time ({{$item->service->booking_gap_time}} minutes)</div>
                        @break
                    @default
                        Disable
                        
                @endswitch
            @else
                Disable
            @endif
        </p>
    </div>
    @if ($item->service && $item->service->is_date && App\Models\BookableItem\BookableItemService::$IS_DATE_ADD_DATE_ONLY == $item->service->is_date)
        <div class="col-6">
            <small class="text-muted">Name of the Date Field</small>
            <p>{{$item->service->date_field_name}}</p>
        </div>
    @endif
    {{-- @if (App\Models\BookableItem\BookableItemService::$IS_DATE_ADD_DATE_AND_TIME_RESTRICTED == $item->service->is_date)
        <div class="col-3">
            <small class="text-muted">From</small>
            <p>{{App\Models\BookableItem\BookableItemService::$HOURS_24h_ARR[$item->service->timeslot_from]}}</p>
        </div>
        <div class="col-3">
            <small class="text-muted">To</small>
            <p>{{App\Models\BookableItem\BookableItemService::$HOURS_24h_ARR[$item->service->timeslot_to]}}</p>
        </div>
    @endif --}}
    <div class="col-6 mt-2">
        <small class="text-muted">Hide Cart functionality</small>
        <p>{{($item->service && $item->service->hide_cart_functionality) ? 'Yes' : 'No'}}</p>
    </div>
    <div class="col-6 mt-2">
        <small class="text-muted">Payment to Aria</small>
        <p>{{($item->service && $item->service->payment_to_aria) ? 'Yes' : 'No'}}</p>
    </div>
    <div class="col-6 mt-2">
        <small class="text-muted">Assign to 3rd-Party Users</small>
        <p>{{($item->service && $item->service->assignTo) ? $item->service->assignTo->fullName() : '-'}}</p>
    </div>

    <div class="col-12 mt-2">
        <h4>Booking policy</h4>
        <div class="row mb-3">
            <div class="col-12">
                <small>Residents can make bookings up to <b>{{$item->prior_to_book_hours}}{{str_plural('hr', $item->prior_to_book_hours)}}</b> before start time.</small>   
            </div>
            <div class="col-12">
                <small>Bookings can be changed or cancelled, up to <b>{{$item->cancellation_cut_off}}{{str_plural('hr', $item->cancellation_cut_off)}}</b> before start.</small>
            </div>
        </div>
    </div>
</div>



