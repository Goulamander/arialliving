@extends('layouts.dashboard')

@section('title', 'My Bookings | '.config('app.name'))

@section('content')
    @include('layouts.messagesTemplate')
    <div class="panel frontEnd">
        <div class="panel-body">
            <h1>Bookings</h1>
            <h4>View or manage your bookings</h4>
            <table class="table data_table bookings">
                <thead>
                    <tr>
                        <th style="width: 10%">ID</th>
                        <th style="width: 35%">Booking</th>
                        <th style="width: 15%">Date/Time</th>
                        <th style="width: 15%">Total</th>
                        <th style="width: 10%">Status</th>
                        <th style="width: 15%">View/Manage</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
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
            iDisplayLength: 25,
            dom: 'lrtip',
            ajax: {
                url: '{{route("resident.api.booking.list")}}',
                method: 'POST'
            },
            columns: [
                {data: 'id', name: 'id'},
                {data: 'title', name: 'title'},
                {data: 'date_time', name: 'date_time'},
                {data: 'total', name: 'total'},
                {data: 'status', name: 'status'},
                {data: 'actions', name: 'actions', orderable: false}
            ],
            oLanguage: {
				sEmptyTable: "You don\'t have any active booking.",
			},
            initComplete: function () {
                this.addClass('ready')
            }
        })
    }
    window.dataTable = init_DataTable()
    </script>
@endsection


@section('modals')
    @include('resident.bookings.modals.booking')
@endsection