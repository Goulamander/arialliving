@extends('layouts.dashboard')

@section('title', 'Bookings | '.config('app.name'))


@section('content')
    @include('layouts.messagesTemplate')

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">

                <div class="header pb-0">
                    <h2><strong>Bookings</strong> list</h2>
                    <div class="row mt-2">
                        <div class="col-sm-12">
                            <ul class="nav nav-modal mt-3">
                                <li class="nav-item active">
                                    <a href="/admin/bookings" class="nav-link @if($tab == '') active @endif">All</a>
                                </li>
                                <li class="nav-item active">
                                    <a href="/admin/bookings/active" class="nav-link @if($tab == 'active') active @endif">Active</a>
                                </li>
                                <li class="nav-item active">
                                    <a href="/admin/bookings/confirmed" class="nav-link @if($tab == 'confirmed') active @endif">Confirmed</a>
                                </li>
                                <li class="nav-item active">
                                    <a href="/admin/bookings/require-action" class="nav-link @if($tab == 'require-action') active @endif">Require Action</a>
                                </li>
                                <li class="nav-item">
                                    <a href="/admin/bookings/complete" class="nav-link @if($tab == 'complete') active @endif">Complete</a>
                                </li>
                                <li class="nav-item">
                                    <a href="/admin/bookings/canceled" class="nav-link @if($tab == 'canceled') active @endif">Canceled</a>
                                </li>
                                {{-- <li class="nav-item">
                                    <a href="/admin/bookings/archive" class="nav-link @if($tab == 'archive') active @endif">Archive</a>
                                </li> --}}
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="body">
                    <table class="table data_table bookings">
                        <thead>
                            <tr>
                                <th style="width: 5%">ID</th>
                                <th style="width: 20%">Title</th>
                                <th style="width: 10%">Dates</th>
                                <th style="width: 10%">Resident</th>
                                <th style="width: 10%">Building</th>

                                <th style="width: 10%">Total</th>
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

        /**
         * init dataTables
         */
        function init_DataTable() 
        {
            return $('.table').dataTable({
                buttons: ['csv'],
                iDisplayLength: 100,
                dom: '<"export_buttons"B>tip',
                ajax: {
                    url: '{{ route("app.booking.list", $tab) }}',
                    method: 'POST'
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'title', name: 'title'},
                    {data: 'date_time', name: 'date_time'},
                    {data: 'user', name: 'user'},
                    {data: 'building', name: 'building'},
                    {data: 'total', name: 'total'},
                    {data: 'status', name: 'status'},
                    {data: 'actions', name: 'actions'}
                ],
                initComplete: function () {
                    //
                    this.addClass('ready');

                    // Create filters
                    const tr = document.createElement('tr')
                    
                    this.api().columns().every(function (index) {
                        var column = this
                        var td = document.createElement('th')

                        switch(index) {

                            // Building filter
                            case 4:
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

                            // Status filter
                            case 6:
                                var choices = [
                                    {val: '', text: 'All'},
                                    {val: {{\App\Models\Booking::$STATUS_ACTIVE}}, text: 'Active'},
                                    {val: {{\App\Models\Booking::$STATUS_CONFIRMED}}, text: 'Confirmed'},
                                    {val: {{\App\Models\Booking::$STATUS_CANCELED}}, text: 'Canceled'},
                                    {val: {{\App\Models\Booking::$STATUS_ARCHIVE}}, text: 'Archive'},
                                ]
                                var select = $('<select>').addClass('form-control').appendTo($(td))

                                $(choices).each(function() {
                                    select.append($("<option>").attr('value', this.val).text(this.text))
                                })
                                select.on('change', function() {
                                    column.search($(this).val()).draw()
                                })
                                select.val('').change()
                                break;   

                            case 7:
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
        //
        window.dataTable = init_DataTable()
    </script>
@endsection


@section('modals')
    {{-- Cancel booking --}}
    @include('app.bookings.modals.cancel')
@endsection