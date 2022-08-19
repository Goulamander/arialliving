@extends('layouts.dashboard')

@section('title', 'Buildings | '.config('app.name'))

@section('page-heading', 'Buildings')


@section('content')

    @include('layouts.messagesTemplate')

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">

                <div class="header pb-0">
                    <h2><strong>Buildings</strong> list</h2>
                    <div class="row mt-2">
                        <div class="col-sm-8"></div>
                        <div class="col-sm-4">
                            <button type="button" data-toggle="modal" data-target="#mod-building" class="btn btn-primary btn-round float-right md-trigger">Add Building</button>
                        </div>
                    </div>
                </div>

                <div class="body">
                    <table class="table data_table buildings">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Name</th>
                                <th style="width: 25%;">Building Managers</th>
                                <th style="width: 20%">No. of Residents</th>
                                <th style="width: 20%">No. of Active Bookings</th>
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
                    url: '{{ route("app.building.list") }}',
                    method: 'POST'
                },
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'managers', name: 'managers',  orderable: false},
                    {data: 'residents_num', name: 'residents_num'},
                    {data: 'bookings_total', name: 'bookings_total', },
                    {data: 'actions', name: 'actions', orderable: false}
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

                            case 2:
                            case 3:
                            case 4:
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
                },
                order: [[ 0, "asc" ]],
            })
        }
        // 
        window.dataTable = init_DataTable()
    </script>
@endsection


@section('modals')
    @if( Auth::user()->isSuperAdmin() )
        @include('app.buildings.modals.add')
    @endif
@endsection
