@extends('layouts.dashboard')

@section('title', 'Items | '.config('app.name'))

@section('content')
    @include('layouts.messagesTemplate')

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">

                <div class="header pb-0">
                    <h2><strong>Bookable Items</strong> list</h2>
                    <div class="row mt-2">
                        <div class="col-sm-8">
                            <ul class="nav nav-modal mt-3">
                                <li class="nav-item active">
                                    <a href="/admin/items" class="nav-link @if($tab == '') active @endif">Active</a>
                                </li>
                                <li class="nav-item">
                                    <a href="/admin/items/archive" class="nav-link @if($tab == 'archive') active @endif">Archive</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-4">
                            <div class="btn-hspace">
                                @if( Auth::user()->hasRole(['super-admin', 'admin', 'building-manager']) )
                                <button type="button" data-toggle="dropdown" class="btn btn-primary btn-arrow btn-round float-right btn-i" aria-expanded="true">
                                    New Item <i class="material-icons">expand_more</i>
                                </button>
                                <ul role="menu" class="dropdown-menu dropdown-menu-right">
                                    <li><button type="button" data-toggle="modal" data-target="#mod-item-room" class="actions md-trigger">Room</button></li>
                                    <li><button type="button" data-toggle="modal" data-target="#mod-item-hire" class="actions md-trigger">Hire</button></li>
                                    <li><button type="button" data-toggle="modal" data-target="#mod-item-event" class="actions md-trigger">Event</button></li>
                                    <li><button type="button" data-toggle="modal" data-target="#mod-item-service" class="actions md-trigger">Service</button></li>
                                </ul>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="body">
                    <table id="data_table" class="table data_table bookable-items">
                        <thead>
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th style="width: 20%">Title/Type</th>
                                <th style="width: 15%">Category</th>
                                <th style="width: 15%">Building</th>
                                <th style="width: 20%">Details</th>
                                <th style="width: 10%">Status</th>
                                <th style="width: 10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

@endsection


@section('scripts')
    <script type="text/javascript">
        function init_DataTable() 
        {
            return DataTable_BookingList = $('#data_table').dataTable({
                buttons: ['csv'],
                iDisplayLength: 100,
                dom: '<"export_buttons"B>tip',
                ajax: {
                    url: '{{route("app.item.list", $tab)}}',
                    method: 'POST'
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'title', name: 'title'},
                    {data: 'category', name: 'category'},
                    {data: 'building', name: 'building'},
                    {data: 'details', name: 'details', orderable: false},
                    {data: 'status', name: 'status'},
                    {data: 'actions', name: 'actions', orderable: false}
                ],
                initComplete: function () {
                    this.addClass('ready');

                    // Create filters
                    const tr = document.createElement('tr')
                    
                    this.api().columns().every(function (index) {
                        var column = this
                        var td = document.createElement('th')

                        switch(index) {

                            // Categories
                            case 2:
                                @php $categories = \App\Models\Category::where('status', 1)->orderBy('order')->get(['id', 'name']); @endphp
                                
                                var choices = {!! json_encode($categories) !!}
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
                                    {val : {{\App\Models\BookableItem::$STATUS_DRAFT}}, text: 'Draft'},
                                    {val : {{\App\Models\BookableItem::$STATUS_ACTIVE}}, text: 'Active'},
                                    {val : {{\App\Models\BookableItem::$STATUS_CANCELLED}}, text: 'Cancelled'},
                                    {val : {{\App\Models\BookableItem::$STATUS_ARCHIVE}}, text: 'Archive'},
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
                    // end filters

                }
            })
        }
        //
        window.dataTable = init_DataTable()
    </script>
@endsection


@section('modals')
    @if( Auth::user()->hasRole(['super-admin', 'admin', 'building-manager']) )
        @include('app.items.event.modals.add')
        @include('app.items.hire.modals.add')
        @include('app.items.room.modals.add')
        @include('app.items.service.modals.add')
    @endif
@endsection
