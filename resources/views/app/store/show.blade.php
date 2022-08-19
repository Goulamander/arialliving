@extends('layouts.dashboard')

@section('title', $store->name.' - Retail Stores | '.config('app.name'))

@section('content')
    @include('layouts.messagesTemplate')
    <div class="row clearfix with_banner">
        @php

        $style = '';
        if(isset($store->images[array_key_first($store->images)])) {
            $style = 'style="background-image: url('.\Storage::url($store->images[array_key_first($store->images)]).')"';
        }

        @endphp 
        <div class="single_top_banner" {!! $style !!}>
            <div class="container">
                <button type="button" data-toggle="modal" data-target="#mod-edit-gallery" class="edit">Edit Gallery</button>
            </div>
        </div>

        {{-- Side Card --}}
        <div class="col-lg-4 col-md-12">
            <div class="card">

                <div class="header">
                    <h2><strong>Retail Store</strong> Profile</h2>
                    <ul class="header-dropdown">
                        <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more"></i> </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="#" data-toggle="modal" data-target="#mod-retail-store" class="md-trigger">Edit</a></li>
                                @if( ! $store->trashed() )
                                <li><a href="{{route('app.store.delete', $store->id)}}" class="actions" data-target="#mod-delete">Delete Store</a></li>
                                @endif
                            </ul>
                        </li>
                    </ul>
                </div>

                <div class="body">

                    <div class="row">
                        <div class="col-12 profile-head">
                            <div class="image-thumb-uploader" data-name="file" data-path="{{$store->imagePath()}}" data-filename="@if($store->is_thumb){{$store->is_thumb}}@endif" data-process-type="thumbnail">
                                @if( $store->thumb ) 
                                <input type="hidden" data-type="local" value="{{ encrypt($store->getThumb()) }}">
                                @endif
                            </div>
                            <h3 class="mb-2">{{$store->name}}</h3>
                            <span class="text-light">{{$store->description}}</span>
                        </div>
                    </div>
                    
                    <h4>Building</h4>
                    @if( $store->building )
                        <div class="building-staff">

                            @if($store->user)
                                <small>Store Admin</small>
                                <h4>
                                    <a href="{{route('app.user.show', $store->user->id)}}" target="_blank">
                                        {{$store->user->fullName()}}
                                    </a>
                                    <span>{{$store->user->email}}</span>
                                </h4> 
                                <hr>
                            @endif

                            @if($store->building->getThumb())
                                <span class="initials _bg float-left mt-1 mr-2" style="background-image: url({{$store->building->getThumb()}})"></span>
                            @endif
                            <h4>{{$store->building->name}}</h4>
                            <small>{{$store->building->suburb}}</small>
                        </div>
                    @else
                    <small>This store has not been attached to any building yet.</small>
                    @endif
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="body">
                    <ul class="nav nav-modal mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link @if($tab == 'deals') active @endif" href="{{route('app.store.show', ['store_id' => $store->id])}}" role="tab">Deals</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link @if($tab == 'redeem-history') active @endif" href="{{route('app.store.redeemHistory.show', ['store_id' => $store->id])}}" role="tab">Redeem History</a>
                        </li>
                    </ul> 
                    <div class="tab-content">
                        @if($tab == 'deals')
                        <div id="deals" class="tab-pane fade show active" role="tabpanel" aria-labelledby="deals-tab">  
                            <div class="row mb-3">
                                <div class="col">
                                    <h3 class="mt-3">Deals</h3>
                                </div>
                                <div class="col-4">
                                    <button type="button" data-toggle="modal" data-target="#mod-retail-deal" class="btn btn-primary btn-sm btn-round float-right md-trigger">Add Deal</button>
                                </div>
                            </div>
                            <table id="data_table" class="table data_table">
                                <thead>
                                    <tr>
                                        <th style="width: 10%">ID</th>
                                        <th style="width: 30%">Deal</th>
                                        <th style="width: 15%">Max. redeem</th>
                                        <th style="width: 15%">Status</th>
                                        <th style="width: 15%">Created at</th>
                                        <th style="width: 15%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        @else
                        <div id="redeem-history" class="tab-pane fade show active" role="tabpanel" aria-labelledby="redeem-history-tab">  
                            <h4>Redeem history</h4>
                            <table id="data_table" class="table data_table">
                                <thead>
                                    <tr>
                                        <th style="width: 15%">Code</th>
                                        <th style="width: 35%">Deal</th>
                                        <th style="width: 30%">Resident</th>
                                        <th style="width: 20%">Redeemed at</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection


@section('modals')
    {{-- Edit Gallery --}}
    @include('app.item.modals.gallery', ['item' => $store])
    {{-- Edit Retail Store --}}
    @include('app.stores.modals.add')
    {{-- Add/Edit Deals --}}
    @include('app.stores.deals.modals.deal')
    {{-- Delete --}}
    @include('app._partials.modals.confirmDelete')
@endsection


@section('scripts')

{{-- Deals table --}}
@if($tab == 'deals')
    <script type="text/javascript">
        function init_DataTable() {
            return $('#data_table').dataTable({
                buttons: ['csv'],
                iDisplayLength: 50,
                dom: '<"export_buttons"B>tip',
                ajax: {
                    method: 'POST',
                    url: '{{ route("app.deal.list", $tab) }}',
                    data: {
                        store_id: {{$store->id}}
                    }
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'deal', name: 'deal'},
                    {data: 'redeem_no', name: 'redeem_no'},
                    {data: 'status', name: 'status'},
                    {data: 'created_date', name: 'created_date'},
                    {data: 'actions', name: 'actions'}
                ],
                initComplete: function () {
                    //
                    this.addClass('ready')

                    // Create filters
                    const tr = document.createElement('tr')
                    
                    this.api().columns().every(function (index) {
                        var column = this
                        var td = document.createElement('th')

                        switch(index) {

                            case 3:
                                var choices = [
                                        {val : '', text: 'All'},
                                        {val : {{\App\Models\RetailDeal::$STATUS_ACTIVE}}, text: 'Active'},
                                        {val : {{\App\Models\RetailDeal::$STATUS_INACTIVE}}, text: 'Inactive'},
                                    ]
                            
                                var select = $('<select>').addClass('form-control').appendTo($(td))
                                    $(choices).each(function() {
                                        select.append($("<option>").attr('value',this.val).text(this.text))
                                    })
                                    select.on('change', function(){
                                        column.search($(this).val()).draw()
                                    })
                                    select.val('').change()
                                break;

                            // No filter  
                            case 4:
                            case 5:
                                break;
                            //
                            default:
                                var input = $('<input>').attr('type', 'text')
                                $(input).addClass('form-control').appendTo($(td))
                                .on('keyup', function () {
                                    column.search($(this).val()).draw()
                                })
                                break
                        }
                        $(tr).append($(td))
                    })
                    $(tr).appendTo(this.find('thead'))
                    // end filters
                    myapp.tippy.init()
                },
                order: [[ 0, "desc" ]],
            })
        }
        window.dataTable = init_DataTable()
    </script>

{{-- Redeem history --}}    
@else 
    <script type="text/javascript">
        function init_DataTable() {
            return $('#data_table').dataTable({
                buttons: ['csv'],
                iDisplayLength: 50,
                dom: '<"export_buttons"B>tip',
                ajax: {
                    method: 'POST',
                    url: '{{ route("app.store.redeemHistory.list", $store->id) }}',
                },
                columns: [
                    {data: 'code', name: 'code'},
                    {data: 'deal', name: 'deal'},
                    {data: 'resident', name: 'resident'},
                    {data: 'date', name: 'date'}
                ],
                initComplete: function () {

                    this.addClass('ready')

                    let tr = document.createElement('tr')
                    
                    this.api().columns().every(function (index) {
                        var column = this
                        var td = document.createElement('th')

                        switch(index) {
                            // No filter  
                            case 3:
                                break;
                            //
                            default:
                                var input = $('<input>').attr('type', 'text')
                                $(input).addClass('form-control').appendTo($(td))
                                .on('keyup', function () {
                                    column.search($(this).val()).draw()
                                })
                                break
                        }
                        $(tr).append($(td))
                    })
                    $(tr).appendTo(this.find('thead'))

                },
                order: [[ 3, "desc" ]],
            })
        }
        window.dataTable = init_DataTable()
    </script>
@endif
@endsection