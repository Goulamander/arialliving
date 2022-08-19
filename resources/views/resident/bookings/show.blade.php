@extends('layouts.dashboard')

@section('title', $booking->getNumber().' - Bookings | '.config('app.name'))

@section('content')

    @include('layouts.messagesTemplate')

    <div class="panel">
        <div class="panel-header"></div>

        <div class="panel-body">
            {{$booking->getNumber()}}
        </div>

    </div>
        
@endsection


@section('scripts')
   
@endsection