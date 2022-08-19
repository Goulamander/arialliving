@extends('layouts.dashboard')

@switch($tab)
    @case('deals')
        @section('title', 'Retail Deals | '.config('app.name'))
        @break

    @case('archive')
        @section('title', 'Retail Stores Archive | '.config('app.name'))
        @break

    @default
        @section('title', 'Retail Stores | '.config('app.name'))
@endswitch

@section('content')
    @include('layouts.messagesTemplate')

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">
                <div class="header pb-0">
                    <h2><strong>Retail Store</strong> list</h2>
                    <div class="row mt-2">
                        <div class="col-sm-8">
                            <ul class="nav nav-modal mt-3">
                                <li class="nav-item active">
                                    <a href="/admin/retail-stores" class="nav-link @if($tab == '') active @endif">Active</a>
                                </li>
                                <li class="nav-item">
                                    <a href="/admin/retail-stores/deals" class="nav-link @if($tab == 'deals') active @endif">Deals</a>
                                </li>
                                <li class="nav-item">
                                    <a href="/admin/retail-stores/archive" class="nav-link @if($tab == 'archive') active @endif">Store Archive</a>
                                </li>
                            </ul>
                        </div>
                        @if(!$tab)
                        <div class="col-sm-4">
                            <button data-toggle="modal" data-target="#mod-retail-store" type="button" class="btn btn-primary btn-round float-right md-trigger">Add Retail Store</button>
                        </div> 
                        @endif
                    </div>
                </div>
                <div class="body">

                    {{-- Deals table --}}
                    @if($tab == 'deals')
                    <table id="data_table" class="table data_table retail-deals">
                        <thead>
                            <tr>
                                <th style="width: 8%">ID</th>
                                <th style="width: 27%">Deal</th>
                                <th style="width: 15%">Store</th>
                                <th style="width: 20%">Building</th>
                                <th style="width: 10%">Max. redeem</th>
                                <th style="width: 10%">Status</th>
                                <th style="width: 10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    {{-- Stores table --}}
                    @else
                    <table id="data_table" class="table data_table retail-stores">
                        <thead>
                            <tr>
                                <th style="width: 25%">Name</th>
                                <th style="width: 10%">Building</th>
                                <th style="width: 15%">Store Manager</th>
                                <th style="width: 15%">No. of deals</th>
                                <th style="width: 15%">Status</th>
                                <th style="width: 10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                    @endif
                </div>
            </div>
        </div>
    </div>

@endsection

@section('modals')
    @if( Auth::user()->isSuperAdmin() )
        {{-- Add Store --}}
        @include('app.stores.modals.add')
    @endif

    {{-- Edit Deals --}}
    @include('app.stores.deals.modals.deal')
@endsection


@section('scripts')
    @if($tab == 'deals')
        <script type="text/javascript">
            function init_DataTable() {
                return $('#data_table').dataTable({
                    buttons: ['csv'],
                    iDisplayLength: 100,
                    dom: '<"export_buttons"B>tip',
                    ajax: {
                        url: '{{ route("app.deal.list", $tab) }}',
                        method: 'POST'
                    },
                    columns: [
                        {data: 'id', name: 'id'},
                        {data: 'deal', name: 'deal'},
                        {data: 'store', name: 'store'},
                        {data: 'building', name: 'building'},
                        {data: 'redeem_no', name: 'redeem_no'},
                        {data: 'status', name: 'status'},
                        {data: 'actions', name: 'actions', orderable: false}
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

                                // Building
                                case 3: 
                                    @php $buildings = \App\Models\Building::myBuildings()->get(['id','name']); @endphp
                                    
                                    var choices = {!! json_encode($buildings) !!}
                                    var select = $('<select>').addClass('form-control').appendTo($(td))

                                    select.append($("<option>").attr('value', '').text('All'))
                                    $(choices).each(function() {
                                        select.append($("<option>").attr('value', this.id).text(this.name))
                                    })
                                    select.on('change', function() {
                                        column.search($(this).val()).draw()
                                    })
                                    select.val('').change()
                                    break;

                                // Status
                                case 5:
                                    var choices = [
                                        {val : '', text: 'All'},
                                        {val : {{\App\Models\User::$STATUS_ACTIVE}}, text: 'Active'},
                                        {val : {{\App\Models\User::$STATUS_INACTIVE}}, text: 'Inactive'},
                                    ]
                                    
                                    var select = $('<select>').addClass('form-control').appendTo($(td))
                                        $(choices).each(function() {
                                            select.append($("<option>").attr('value',this.val).text(this.text))
                                        })
                                        select.on('change', function(){
                                            column.search($(this).val()).draw()
                                        })
                                        select.val('').change()
                                    break

                                // No filter  
                                    case 4:
                                    case 6:
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
                    order: [[ 0, "asc" ]],
                })
            }
            window.dataTable = init_DataTable()
        </script>

    @else

        <script type="text/javascript">
            function init_DataTable() {
                return $('#data_table').dataTable({
                    buttons: ['csv'],
                    iDisplayLength: 100,
                    dom: '<"export_buttons"B>tip',
                    ajax: {
                        url: '{{ route("app.store.list", $tab) }}',
                        method: 'POST'
                    },
                    columns: [
                        {data: 'name', name: 'name'},
                        {data: 'building', name: 'building'},
                        {data: 'store_manager', name: 'store_manager', orderable: false},
                        {data: 'deals_no', name: 'deals_no'},
                        {data: 'status', name: 'status'},
                        {data: 'actions', name: 'actions', orderable: false}
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

                                // Building
                                case 1: 
                                    @php $buildings = \App\Models\Building::myBuildings()->get(['id','name']); @endphp
                                    
                                    var choices = {!! json_encode($buildings) !!}
                                    var select = $('<select>').addClass('form-control').appendTo($(td))

                                    select.append($("<option>").attr('value', '').text('All'))
                                    $(choices).each(function() {
                                        select.append($("<option>").attr('value', this.id).text(this.name))
                                    })
                                    select.on('change', function() {
                                        column.search($(this).val()).draw()
                                    })
                                    select.val('').change()
                                    break;

                                // Status
                                case 4:
                                    var choices = [
                                        {val : '', text: 'All'},
                                        {val : {{\App\Models\User::$STATUS_ACTIVE}}, text: 'Active'},
                                        {val : {{\App\Models\User::$STATUS_INACTIVE}}, text: 'Inactive'},
                                    ]
                                    
                                    var select = $('<select>').addClass('form-control').appendTo($(td))
                                        $(choices).each(function() {
                                            select.append($("<option>").attr('value',this.val).text(this.text))
                                        })
                                        select.on('change', function(){
                                            column.search($(this).val()).draw()
                                        })
                                        select.val('').change()
                                    break

                                // No filter  
                                    case 3:
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
                    },
                    order: [[ 0, "asc" ]],
                })
            }
            window.dataTable = init_DataTable()
        </script>
    @endif
@endsection