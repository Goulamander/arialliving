@extends('layouts.dashboard')

@section('title', $item->title.' | '.config('app.name'))


@section('top-section')
<section class="booking-single--slider">
    @if($item->images)
    <div class="__slider">
        @foreach($item->images as $image)
            <div class="__slide" style="background-image: url('{{\Storage::url($image)}}')"></div>
        @endforeach
    </div>
    @endif
</section>
@endsection


@section('content')

    @include('layouts.messagesTemplate')
    @php
        $is_hide_cart_functionality = $item->type != \App\Models\BookableItem::$TYPE_SERVICE || ($item->type == \App\Models\BookableItem::$TYPE_SERVICE && !$item->service->hide_cart_functionality);
    @endphp
    <div class="booking-single--body">
        {{-- Booking Card --}}
        @if ($is_hide_cart_functionality)
        <div id="booking-card--wrap" class="booking-card--wrap">
            <div class="card-inner-wrap">
                <div class="card booking-card">
                    <div class="header">
                        @if($item->type == \App\Models\BookableItem::$TYPE_SERVICE)
                        <h3>Your Cart</h3>
                        @else
                        <h3>Make a booking</h3>
                        @endif
                    </div>
                    <div class="body">
                        <form method="POST" id="BookingForm" action="{{route('app.resident.booking.create', [\App\Models\BookableItem::$TYPE_LABEL[$item->type], $item->id])}}" data-encrypt="true">
                        @switch($item->type)

                            {{-- Event --}}
                            @case(\App\Models\BookableItem::$TYPE_EVENT)
                                @include('resident.item.partials.event')
                                @break

                            {{-- Room --}}
                            @case(\App\Models\BookableItem::$TYPE_ROOM)
                                @include('resident.item.partials.room')
                                @break

                            {{-- Hire --}}
                            @case(\App\Models\BookableItem::$TYPE_HIRE)
                                @include('resident.item.partials.hire')
                                @break

                            {{-- Service --}}
                            @case(\App\Models\BookableItem::$TYPE_SERVICE)
                                @include('resident.item.partials.service')
                                @break
                                
                        @endswitch
                        </form>
                    </div>   
                </div>
            </div>
        </div>
        @endif

        {{-- Booking Content --}}
        <div id="booking-single--main" class="booking-single--main {{!$is_hide_cart_functionality ? 'w-100' : ''}}">
            <div class="booking-single--content">

                <div class="booking-single--item-title">
                    <h3>{{$item->category->name}}</h3>
                    <h1>{{$item->title}}</h1>
                </div>

                <div class="booking-content">
                    @if($item->type == \App\Models\BookableItem::$TYPE_SERVICE)
                        {{-- Service: Line items --}}
                        @if(!$item->line_items->isEmpty())
                            <div id="cart_line_items" class="line-items">
                            @foreach($item->line_items as $lineItem)
                             
                                @php 
                                if($lineItem->status != 1) {
                                    continue;
                                }
                                @endphp
                                <div class="_item card">
                                    <div class="body">
                                        @if($lineItem->thumb)
                                            <img class="thumb" src="{{$lineItem->getThumb()}}" alt="{{$lineItem->name}}"/>
                                        @endif
                                        <div class="title_price">
                                            <h3>{{$lineItem->name}}</h3>
                                            @if (!!$item->service->hide_pricing)
                                                <span class="price"></span>
                                            @else
                                                <span class="price">{{($lineItem->price > 0 && !Auth::user()->isResidentVip()) ? priceFormat($lineItem->price) : priceFormat(0)}}</span>
                                            @endif
                                        </div>
                                        <div class="add_to_cart">
                                            @if ($is_hide_cart_functionality)
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control _qty" name="add_to_cart_qty" min="1" max="100" placeholder="QTY" value="1" aria-label="QTY">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-primary btn-round" name="add_to_cart" value="{{$lineItem->id}}">Add</button>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="description">
                                            {{$lineItem->desc}}
                                        </div>
                                    </div>
                                </div>
                           
                            @endforeach
                            </div>
                        @endif
                    @endif

                    @if($item->tags)
                    <div class="booking-tags">
                        @foreach($item->tags as $tag)
                            <span class="tag">{{$tag}}</span>
                        @endforeach
                    </div>
                    @endif

                    {!! $item->description !!}

                    @if($building)
                    <div class="building-contact">
                        <h3>Any Questions?</h3>
                        <h5>{{$building->name}} Onsite Management</h5>
                        
                        <small>Contact</small><br>

                        <h5>{{$building->contact_name}}</h5>
                        <ul>
                            @if($building->phone)
                                <li><a href="tel:{{$building->phone}}"><i class="icon-screen-smartphone"></i> {{$building->phone}}</a></li>
                            @endif    
                            @if($building->mobile)
                                <li><a href="tel:{{$building->mobile}}"><i class="icon-screen-smartphone"></i> {{$building->mobile}}</a></li>
                            @endif
                            @if($building->email)
                                <li class="email"><a href="mailto:{{$building->email}}">{{$building->email}}</a></li>
                            @endif
                        </ul>

                        <h5>Office hours</h5>
                        <ul class="opening-hours">
                        @php
                            $office_hours = $item->office_hours ? json_decode($item->office_hours) : $item->building->office_hours;
                        @endphp     
                        @foreach($office_hours as $day => $hours)
                            @if( ! $hours->status )
                                <li><b>{{$day}}</b> Closed</li>
                            @else
                                <li><b>{{$day}}</b> {{$hours->from}} - {{$hours->to}}</li>
                            @endif
                          
                        @endforeach
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>  

@endsection

@section('modals')
    @include('resident.item.partials._additional_password_prompt_confirm')
@endsection

@section('scripts_before_libs')
    {{-- Check additional_password_prompt 100$ --}}
    @php 

    $additional_password_prompt_show = true;
    $additional_password_prompt_limit = App\Models\UserSetting::$ADDITIONAL_PASSWORD_PROMPT_LIMIT;

    if($user->settings && $user->settings->additional_password_prompt == 0) {
        $additional_password_prompt_show = false;
    }

    @endphp
    <script type="text/javascript">
        $(document).ready( function() {
            $(document).on('click', '#BookingForm button[type="submit"]', function(e)  { // Validate confirm password form
                e.preventDefault();
                let form = $(this).parents('form')
                let form_data = myapp.form.collectInputs(form);
                let additional_password_prompt_show = Boolean('{{$additional_password_prompt_show}}');
                let additional_password_prompt_limit = Number('{{$additional_password_prompt_limit}}');
                if(form_data && form_data._price && Number(form_data._price) > additional_password_prompt_limit && additional_password_prompt_show){
                    e.stopImmediatePropagation();
                    $('#mod-password-confirm').modal('show');
                    $('#ConfirmPasswordForm #password').val('');
                    return false;
                }
            });
        })
    </script>

@endsection

@php
    use App\Models\BookableItem\BookableItemService;
    use App\Models\BookableItem;
@endphp

@section('scripts')

{{-- room / hire --}}
@if(in_array($item->type, [BookableItem::$TYPE_ROOM, BookableItem::$TYPE_HIRE]))

    @php
    
    $allow_multiday = $item->hire ? $item->hire->allow_multiple : $item->room->allow_multiday; 
    $max_length_of_booking = $item->hire ? $item->hire->booking_max_length : $item->room->booking_max_length;

    @endphp
    <script type="text/javascript">
        
        let cal = window.ResidentBooking,
            _disabled_dates = [{!! '"'.implode('","',$dates['unavailable']).'"' !!}],
            _disabled_dates__with_resident = [{!! '"'.implode('","', array_merge($dates['unavailable'], $dates['disabled_duplicate_book_same_date_by_resident'])).'"' !!}],
            _disabled_dates_with_range = [{!! '"'.implode('","', array_merge($dates['unavailable'], $dates['disabled_for_full_range'])).'"' !!}],
            _disabled_low_availability = [{!! '"'.implode('","',$dates['low_availability']).'"' !!}]

        cal.config.mode = '{{$allow_multiday == true ? 'range' : 'single'}}'
        // cal.config.disable = _disabled_dates
        // const disable_office_date = date => [{!! '"'.implode('","',$dates['disabled_date_by_office_hours']).'"' !!}].map(v => Number(v)).includes(date.getDay());
        const disable_office_date = date => null;

        cal.config.disable = [..._disabled_dates__with_resident, disable_office_date]
        cal.config.dateFormat = "Y-m-d"

        cal.config.onDayCreate.push(function(dObj, dStr, fp, dayElem) {
            let date = dayElem.getAttribute('aria-label')
                date = moment(dayElem.getAttribute('aria-label'), 'MMMM DD, YYYY').format('YYYY-MM-DD')

            if(_disabled_dates.includes(date)) {
                if($(dayElem).hasClass('flatpickr-disabled')) {
                    $(dayElem).addClass('full_availability').removeClass('flatpickr-disabled');
                }
                dayElem.innerHTML += "<span class='day-status full'></span>";
            } else if(_disabled_low_availability.includes(date)) {
                if($(dayElem).hasClass('flatpickr-disabled')) {
                    $(dayElem).addClass('low_availability').removeClass('flatpickr-disabled');
                }
                dayElem.innerHTML += "<span class='day-status low'></span>";
            }
        })

        let a = [];

        cal.config.onChange.push(function(dObj, dStr, fp, dayElem) {
            // when they try to click on a calendar date that is blocked out (due to other bookings),
            if([..._disabled_low_availability, ..._disabled_dates].includes(dStr)) {
                $("#bookingDetailsTab").collapse('hide');
                $("button[name='complete_booking']").attr('disabled', true);
                sc.alert.show('alert-danger', 'This day is booked out. Please choose a different day.', 5000);
                return false;
            }
            // check if select is outside business hours, don't allow the booking
            const _today = moment();
            const _select_date = moment(dStr);
            const _office_hours_dates = [{!! '"'.implode('","',$dates['disabled_date_by_office_hours']).'"' !!}];
            const today_date_is_outside_office_hours = _office_hours_dates.includes(String(_today.day())) // If today's date is outside office hours
            const is_outside_hours = _office_hours_dates.map(v => Number(v)).includes(dObj[0].getDay()); // if selected booking date is outside booking hours
            const duration_today_and_select_date = moment.duration(_select_date.diff(_today)).asHours() < 120; // if selected booking date is less than 120hrs from today's date
            if (today_date_is_outside_office_hours && is_outside_hours && duration_today_and_select_date) {
                $("#bookingDetailsTab").collapse('hide');
                $("button[name='complete_booking']").attr('disabled', true);
                sc.alert.show('alert-danger', 'Unable to proceed with booking. Please book during office hours.', 5000);
                return false;
            }

            $("#bookingDetailsTab").collapse('show');

       
            if(cal.config.mode !== 'range') return 

            if(a.length < 2) {

                if(a.length == 0) {

                    @if($allow_multiday && $max_length_of_booking)
                        const a_selectable_range = moment(dStr).add({{$max_length_of_booking}}+24, 'hours').format('YYYY-MM-DD')
                        const b_selectable_range = moment(dStr).add(-({{$max_length_of_booking}}+24), 'hours').format('YYYY-MM-DD')
                        
                        cal.config.disable = [..._disabled_dates_with_range, a_selectable_range, b_selectable_range, disable_office_date]
                    @else
                        cal.config.disable = [..._disabled_dates_with_range, disable_office_date]
                    @endif
                }
                else {
                    cal.config.disable = [..._disabled_dates, disable_office_date]
                }

                a.push(dStr)
            }
            else {
                a = [dStr]
                if( ! _disabled_dates_with_range.includes(dStr) ) {
                    @if($allow_multiday && $max_length_of_booking)
                        
                        const a_selectable_range = moment(dStr).add({{$max_length_of_booking}}+24, 'hours').format('YYYY-MM-DD')
                        const b_selectable_range = moment(dStr).add(-({{$max_length_of_booking}}+24), 'hours').format('YYYY-MM-DD')
                        
                        cal.config.disable = [..._disabled_dates_with_range, a_selectable_range, b_selectable_range, disable_office_date]
                    @else
                        cal.config.disable = [..._disabled_dates_with_range, disable_office_date]
                    @endif
                }
            }
            cal.redraw()
        })

        cal.redraw()
    </script>

{{-- service --}}
@elseif($item->type == BookableItem::$TYPE_SERVICE && $item->service->is_date)
    <script>
        const cal = window.ResidentBooking
        cal.config.mode = 'single'
    </script>
    @if (in_array($item->service->is_date, [BookableItemService::$IS_DATE_ADD_DATE_AND_TIME, BookableItemService::$IS_DATE_ADD_DATE_AND_TIME_RESTRICTED, BookableItemService::$IS_DATE_ADD_TIMESLOT,  BookableItemService::$IS_DATE_ADD_DATE_RESTRICTED]))
        <script>
            const _service_office_hours = JSON.parse(@json($item->office_hours ? $item->office_hours : ($item->building->office_hours ? $item->building->office_hours : [])));
            const _is_service_date_time = !!'{!! $item->service->is_date == BookableItemService::$IS_DATE_ADD_DATE_AND_TIME !!}';
            if(!_is_service_date_time){ // check disable only timeslot selection
                const name_of_date = @json(BookableItemService::$NAME_OF_DATE);
                let _disable_by_office_hours = [];
                name_of_date.map((v, k) => {
                    if(_service_office_hours[v]['status'] == 1){
                        _disable_by_office_hours = [..._disable_by_office_hours, k == 6 ? 0 : k + 1]
                    }
                });
    
                const _disable_by_office_hours_ = date => {
                    return !_disable_by_office_hours.includes(date.getDay());
                }
                cal.config.disable = [_disable_by_office_hours_];
            }

            // calendar change
            cal.config.onChange.push(function(dObj, dStr, fp, dayElem) {
                $("#bookingDetailsTab").collapse('show');
                cal.redraw()
            })
            cal.redraw()
        </script>
    @endif
@endif
    <script src="{{ asset('/js/eWay.js')}}"></script>
@endsection
