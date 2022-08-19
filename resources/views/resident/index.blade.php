@extends('layouts.dashboard')

@section('title', $resident_building->name.' | '.config('app.name'))

@section('content')
    @include('layouts.messagesTemplate')
    <div class="panel resident__front-end">
        <div class="panel-header"></div>
        <div class="panel-body">
            {{-- Global Search --}}
            <form class="row">
                <div class="form-group _title col-4 offset-8">
                    <input id="global-search" name="global-search" class="form-control" placeholder="Search service or bookable item ..." autocomplete="off">
                </div>
            </form>
            <div id="items-content">
                @include('resident._items')
            </div>
        </div>
    </div> 
@endsection

@section('scripts')
@endsection

@section('modals')
    {{-- View booking --}}
    @include('resident.bookings.modals.booking')
@endsection

