@php
    use App\Models\BookableItem\BookableItemService;
@endphp

<div id="bookingCalendar" class="calendar"></div>
<input type="hidden" name="date_start"/>
<input type="hidden" name="date_end"/>

{{-- @if($item_type != 'service') --}}
@php
    $is_service = $item_type == 'service';
    $service_is_date = false;
    $service_is_date_timeslot = false;
    if($is_service) {
        $service_is_date = in_array($item->service->is_date, [BookableItemService::$IS_DATE_NO_DATE, BookableItemService::$IS_DATE_ADD_DATE_ONLY, BookableItemService::$IS_DATE_ADD_DATE_RESTRICTED]);
        $service_is_date_timeslot = $item->service->is_date == BookableItemService::$IS_DATE_ADD_TIMESLOT;
    }
@endphp
@if(!($is_service && $service_is_date))
<div id="bookingDetailsTab" class="collapse">
    <h3 class="c-line"><span>Your booking details</span></h3>
    <div class="date">
        <span id="booking_date">-</span>
    </div>
    <div class="row">
        <div class="col-4">
            <small>@if($item_type == 'hire') Pickup @else From @endif</small>
            <select class="form-control" id="booking_from" name="time_start" class="booking_from">
                <option value="">From</option>
            </select>
        </div>
        <div class="col-4">
            <small>@if($item_type == 'hire') Drop-off @else To @endif</small>
            <select class="form-control" id="booking_to" name="time_end" class="booking_to" {{$service_is_date_timeslot ? 'disabled' : ''}}>
                <option value="">To</option>
            </select>
        </div>
        <div class="col-4">
            <small>Length</small>
            <span id="booking_length" class="booking_length mt-2">-</span>
            <input type="hidden" name="booking_length"/>
            <input type="hidden" name="booking_length_unit"/>
        </div>
        @switch($item_type)
            @case('room')
                @if ($item->bookableItemFees()->exists())
                    @if ($user->isSuperAdmin() || $user->isAdmin())
                        <div class="col-12 mt-4">
                            <div class="checkbox text-left mb-0">
                                <input type="checkbox" id="cleaning_required" name="cleaning_required" class="" value="1" data-parsley-multiple="cleaning_required">
                                <label for="cleaning_required">Cleaning required</label>
                            </div>
                        </div>
                    @else
                        <div class="col-12 mt-3">
                            <small class="text-left">Cleaning Fee</small>
                            <div class="form-group text-left">
                                @foreach ($item->bookableItemFees()->get() as $key => $fee)
                                    <div class="radio mb-1">
                                        <input type="radio" id="booking_cleaning_fee_{{$key}}" name="booking_cleaning_fee" value="{{$fee}}" @if($loop->first) checked @endif />
                                        <label class="form-check-label" for="booking_cleaning_fee_{{$key}}">{{$fee->name}} ({{priceFormat($fee->fee)}})</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
                @break
            @case('hire')
                @if (isset($item->hire->available_qty) && $item->hire->available_qty > 0)
                    <div class="col-4 mt-2">
                        <small>QTY</small>
                        <select class="form-control" id="booking_qty" name="booking_qty" class="booking_qty">
                            @foreach (range(1, $item->hire->available_qty) as $i)
                                <option value="{{$i}}">{{$i}}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @break
            @default
                
        @endswitch
        
    </div>
</div>
@endif