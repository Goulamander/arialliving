@extends('layouts.dashboard')

@section('title', 'Calendar | '.config('app.name'))

@section('content')

    @include('layouts.messagesTemplate')

    <style>
        section.content:before {
            top: 60px;
        }
    </style>

    <div class="body admin_calendar_page">
        <div id="calendar_filters" class="calendar_header">
            <strong>Filters</strong>
            @if(Auth::user()->hasRole(['super-admin', 'admin']))
            <div class="form_group building_picker">
                <select name="building_filter" class="form-control s2">
                <option value="all">All Buildings</option>
                @if($buildings)
                    @foreach($buildings as $building)
                        @php $selected = $buildings[0]->id == $building->id ? 'selected' : '' @endphp
                        <option value="{{$building->id}}" {{$selected}}>{{$building->name}}</option>
                    @endforeach
                @endif
                </select>
            </div>
            @endif
            <div class="form_group category_picker">
                <select name="category_filter" class="form-control">
                    <option value="all">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{$category->id}}">{{$category->name}}</option>
                    @endforeach
                </select>
            </div>
            @if(Auth::user()->hasRole(['super-admin', 'admin']))
            <div class="form_group item_picker">
                <select name="item_filter" class="form-control">
                    <option value="all">All Items</option>
                    @foreach($_items as $item)
                        @if($item['children']) 
                            <optgroup label="{{$item['text']}}">
                            @foreach($item['children'] as $itm)
                                <option value="{{$itm['id']}}" data-title="{{$itm['title']}}">{{$itm['title']}}</option>
                            @endforeach
                            </optiongroup>
                        @endif
                    @endforeach
                </select>
                
            </div>
            <button class="btn btn-primary btn-round btn-sm" type="button" id="add_booking">Add Booking</button>
            @endif
        </div>
        <div id="calendar"></div>
    </div>
@endsection

@section('scripts_before_libs')
<script type="text/javascript">
    window.Laravel.calendar = {!! json_encode([
        'default_building' => [
            'id' => ! $buildings->isEmpty() ? $buildings[0]->id : '',
            'name' => ! $buildings->isEmpty() ? $buildings[0]->name : '',
        ] 
    ]) !!}
</script>
@endsection


@section('modals')
    {{-- View booking --}}
    @include('app.booking.card')
@endsection
