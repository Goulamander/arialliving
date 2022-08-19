<?php
use \App\Models\BookableItem\BookableItemEvent;

?>

{{-- Loop the active bookings --}}
@if(!$bookings->isEmpty())
<h4>Your Active Bookings</h4>
<div class="embla item__slider">
    <div class="embla__dots"></div>
    <div class="embla__viewport">
        <div class="embla__container">
            @foreach($bookings as $item)
            <div class="embla__slide">
                @php
                // $item->bookableItem->thumb = $item->bookableItem && $item->bookableItem->is_thumb ? $item->bookableItem->getThumb('820x500') : '';
                $item_bookableItem_thumb = $item->bookableItem->is_thumb ? $item->bookableItem->getThumb('820x500') : '';
                @endphp
                <div class="item__card booking" style="background-image: url('{{$item_bookableItem_thumb ?? ''}}')">
                    <a data-open-booking="{{$item->id}}">
                        <div class="card_body">
                            <h3>{{$item->getNumber()}}</h3>
                            <div class="booking-date">
                                <span class="date">{{bookingDate($item->start, $item->end)}}</span>
                                <span class="time">{{bookingTime($item->start, $item->end)}}</span>
                            </div>
                            <div class="booking-title">
                                <span>{{$item->bookableItem->getTypeLabel()}}</span>
                                <h4>{{$item->bookableItem->title}}</h4>
                            </div>
                        </div> 
                        <div class="card_footer">
                            <span class="book">Manage</h3>
                        </div>
                    </a>   
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <button class="embla__button embla__button--prev" type="button">
        <svg class="embla__button__svg" viewBox="137.718 -1.001 366.563 643.999">
            <path d="M428.36 12.5c16.67-16.67 43.76-16.67 60.42 0 16.67 16.67 16.67 43.76 0 60.42L241.7 320c148.25 148.24 230.61 230.6 247.08 247.08 16.67 16.66 16.67 43.75 0 60.42-16.67 16.66-43.76 16.67-60.42 0-27.72-27.71-249.45-249.37-277.16-277.08a42.308 42.308 0 0 1-12.48-30.34c0-11.1 4.1-22.05 12.48-30.42C206.63 234.23 400.64 40.21 428.36 12.5z"></path>
        </svg>
    </button>
    <button class="embla__button embla__button--next" type="button">
        <svg class="embla__button__svg" viewBox="0 0 238.003 238.003">
            <path d="M181.776 107.719L78.705 4.648c-6.198-6.198-16.273-6.198-22.47 0s-6.198 16.273 0 22.47l91.883 91.883-91.883 91.883c-6.198 6.198-6.198 16.273 0 22.47s16.273 6.198 22.47 0l103.071-103.039a15.741 15.741 0 0 0 4.64-11.283c0-4.13-1.526-8.199-4.64-11.313z"></path>
        </svg>
    </button>
</div>
@endif

{{-- All empty --}}
@if(!$sliders)
<div class="embla item__slider">
    <div class="embla__dots"></div>
    <div class="embla__viewport">
        <div class="embla__container">
            <div class="embla__slide"><div class="item__card_placeholder"></div></div>
            <div class="embla__slide"><div class="item__card_placeholder"></div></div>
            <div class="embla__slide"><div class="item__card_placeholder"></div></div>
        </div>
    </div>
    <button class="embla__button embla__button--prev" type="button">
        <svg class="embla__button__svg" viewBox="137.718 -1.001 366.563 643.999">
            <path d="M428.36 12.5c16.67-16.67 43.76-16.67 60.42 0 16.67 16.67 16.67 43.76 0 60.42L241.7 320c148.25 148.24 230.61 230.6 247.08 247.08 16.67 16.66 16.67 43.75 0 60.42-16.67 16.66-43.76 16.67-60.42 0-27.72-27.71-249.45-249.37-277.16-277.08a42.308 42.308 0 0 1-12.48-30.34c0-11.1 4.1-22.05 12.48-30.42C206.63 234.23 400.64 40.21 428.36 12.5z"></path>
        </svg>
    </button>
    <button class="embla__button embla__button--next" type="button">
        <svg class="embla__button__svg" viewBox="0 0 238.003 238.003">
            <path d="M181.776 107.719L78.705 4.648c-6.198-6.198-16.273-6.198-22.47 0s-6.198 16.273 0 22.47l91.883 91.883-91.883 91.883c-6.198 6.198-6.198 16.273 0 22.47s16.273 6.198 22.47 0l103.071-103.039a15.741 15.741 0 0 0 4.64-11.283c0-4.13-1.526-8.199-4.64-11.313z"></path>
        </svg>
    </button>
</div>
@endif

{{-- Loop the item sliders --}}
@foreach($sliders as $slider)
    @if(count($slider['items']) > 0)
    <h2>{{$slider['title']}}</h2>
    <div class="embla item__slider">
        <div class="embla__dots"></div>
        <div class="embla__viewport">
            <div class="embla__container">
                @php

                    $slider_item = $slider['items']->sortBy(function($item, $key) {
                        if($item->event) { // sort by event dates
                            return $item->event->event_date;
                        }
                        return;
                    })->sortBy(function($item, $key) {
                        if($item->event && $item->recurring) { // sort by event dates
                            return $item->recurring->repeat_next;
                        }
                        return;
                    });

                    $now = \Carbon\Carbon::now()->hour(0)->minute(0)->second(0)->subDays(1);
                    foreach ($slider_item as $key => $item) {
                        if($item->event) {
                            if($item->event->event_type == BookableItemEvent::$TYPE_SINGLE) {
                                $event_date = \Carbon\Carbon::parse($item->event->event_date)->hour(0)->minute(0)->second(0);
                                if($now->gte($event_date)){
                                    unset($slider_item[$key]);
                                }
                            } 
                            else if ($item->event->event_type == BookableItemEvent::$TYPE_REPEATING) {
                                $repeat_next = \Carbon\Carbon::parse($item->recurring->repeat_next)->hour(0)->minute(0)->second(0);
                                if($now->gte($repeat_next)){
                                    unset($slider_item[$key]);
                                }
                            }
                        }
                    }
                @endphp
                @if (count($slider_item))
                    @foreach($slider_item as $item)
                    <div class="embla__slide">
                        <div class="item__card {{$item->event ? 'event' : ''}}" style="background-image: url('{{$item->thumb ?? ''}}')">
                            <a href="/items/{{$item->type_label}}/{{$item->id}}">
                                @if($item->event)
                                    @if($item->event->event_type == BookableItemEvent::$TYPE_SINGLE)
                                        <div class="card_event_header">
                                            <div class="date">
                                                <span class="days">{{dateFormat($item->event->event_date, 'd')}}</span>
                                                <span class="month">{{dateFormat($item->event->event_date, 'M')}}</span>
                                                <span class="year">{{dateFormat($item->event->event_date, 'Y')}}</span>
                                            </div>
                                            <div class="float-right">
                                                @if (isset($item->event->event_from))
                                                    <div class="time">
                                                        <small>From</small>
                                                        <span class="time">{{timeFormat($item->event->event_from)}}</span>
                                                    </div>
                                                    <div class="length">
                                                        <small>Length</small>
                                                        <span class="length">{{timeLength($item->event->event_from, $item->event->event_to)}}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @elseif($item->event->event_type == BookableItemEvent::$TYPE_REPEATING)
                                        <div class="card_event_header">
                                            <div class="date">
                                                <span class="days">{{dateFormat($item->recurring->repeat_next, 'd')}}</span>
                                                <span class="month">{{dateFormat($item->recurring->repeat_next, 'M')}}</span>
                                                <span class="year">{{dateFormat($item->recurring->repeat_next, 'Y')}}</span>
                                            </div>
                                            <div class="float-right">
                                                @if (isset($item->event->event_from))
                                                    <div class="time">
                                                        <small>From</small>
                                                        <span class="time">{{timeFormat($item->event->event_from)}}</span>
                                                    </div>
                                                    <div class="length">
                                                        <small>Length</small>
                                                        <span class="length">{{timeLength($item->event->event_from, $item->event->event_to)}}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                @endif

                                <div class="card_body">

                                    <h3>{{$item->title}}</h3>

                                    @if($item->room && $item->room->low_availability)
                                    <span class="status low">Low Availability</span>
                                    @endif

                                    @if($item->hire && $item->hire->low_availability)
                                    <span class="status low">Low Availability</span>
                                    @endif

                                    @if($item->event && $item->event->low_seats !== NULL)
                                        @if($item->event->low_seats == 0)
                                        <span class="status low">Event Full</span>
                                        @else
                                        <span class="status low">{{$item->event->low_seats}} {{str_plural('Seat', $item->event->low_seats)}} Left</span>
                                        @endif
                                    @endif

                                </div>

                                <div class="card_footer">
                                    @if(!$item->is_free)
                                    <div class="pricing">
                                        {!! $item->price_html !!}
                                    </div>
                                    @endif
                                    <span class="book">{{$item->hire ? 'Order' : 'Book'}}</span>
                                </div>

                            </a>
                        </div>
                    </div>

                    @endforeach
                @else
                    <div class="p-2">No {{$slider['title']}} found</div>
                @endif
            </div>
        </div>
        <button class="embla__button embla__button--prev" type="button">
            <svg class="embla__button__svg" viewBox="137.718 -1.001 366.563 643.999">
                <path d="M428.36 12.5c16.67-16.67 43.76-16.67 60.42 0 16.67 16.67 16.67 43.76 0 60.42L241.7 320c148.25 148.24 230.61 230.6 247.08 247.08 16.67 16.66 16.67 43.75 0 60.42-16.67 16.66-43.76 16.67-60.42 0-27.72-27.71-249.45-249.37-277.16-277.08a42.308 42.308 0 0 1-12.48-30.34c0-11.1 4.1-22.05 12.48-30.42C206.63 234.23 400.64 40.21 428.36 12.5z"></path>
            </svg>
        </button>
        <button class="embla__button embla__button--next" type="button">
            <svg class="embla__button__svg" viewBox="0 0 238.003 238.003">
                <path d="M181.776 107.719L78.705 4.648c-6.198-6.198-16.273-6.198-22.47 0s-6.198 16.273 0 22.47l91.883 91.883-91.883 91.883c-6.198 6.198-6.198 16.273 0 22.47s16.273 6.198 22.47 0l103.071-103.039a15.741 15.741 0 0 0 4.64-11.283c0-4.13-1.526-8.199-4.64-11.313z"></path>
            </svg>
        </button>
    </div>
    @endif
@endforeach