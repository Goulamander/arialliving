@extends('layouts.dashboard')

@section('title', 'Redeemed Deals | '.config('app.name'))

@section('content')
    @include('layouts.messagesTemplate')
    <div class="panel frontEnd">
        <div class="panel-body">
            <h1 class="mb-5">Your Redeem History</h1>
            <table class="table data_table bookings">
                <thead>
                    <tr>
                        <th style="width: 10%">Code</th>
                        <th style="width: 30%">Deal</th>
                        <th style="width: 30%">Store</th>
                        <th style="width: 30%">Date</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection


@section('scripts')
<script type="text/javascript">

    function init_DataTable() 
    {
        return $('.table').dataTable({
            iDisplayLength: 25,
            dom: 'lrtip',
            ajax: {
                url: '{{route("resident.api.redeems.list")}}',
                method: 'POST'
            },
            columns: [
                {
                    data: 'code', 
                    name: 'code',
                    orderable: false},
                {
                    data: 'deal', 
                    name: 'deal',
                    orderable: false},
                {
                    data: 'store', 
                    name: 'store',
                    orderable: false},
                {
                    data: 'date', 
                    name: 'date', 
                    orderable: false
                }
            ],
            order: [[ 3, "desc" ]], 
            oLanguage: {
				sEmptyTable: "You haven\'t redeem any deal yet.",
			},
            initComplete: function () {
                this.addClass('ready')
            }
        })
    }
    window.dataTable = init_DataTable()
</script>
@endsection