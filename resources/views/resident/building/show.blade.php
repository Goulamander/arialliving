@extends('layouts.dashboard')

@section('title', 'Building | '.config('app.name'))

@section('top-section')
<section class="booking-single--slider">
    @if($building->images)
    <div class="__slider">
        @foreach($building->images as $image)
            <div class="__slide" style="background-image: url('{{\Storage::url($image)}}')"></div>
        @endforeach
    </div>
    @endif
</section>
@endsection

@section('content')

    @include('layouts.messagesTemplate')

    <div class="booking-single--body building-page">

        <div class="booking-single--main">
            <div class="booking-single--content mt-0">

                <div class="booking-single--item-title mb-0">
                    <h3>{{$building->suburb}}</h3>
                    <h1>{{$building->name}}</h1>
                </div>

                <div class="building_content">
                    <div class="row">

                        <div class="col-lg-8">

                            <div class="card">
                                <div class="html_content">{!! $building->page_content->content ?? '' !!}</div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            @if($building)
                            <div class="building-contact">
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
                                @foreach($building->office_hours as $day => $hours)
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
        </div>
    </div>

@endsection


@section('scripts')
@endsection    