@extends('layouts.dashboard')

@section('title', 'Residents | '.config('app.name'))


@section('content')
    @include('layouts.messagesTemplate')

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">

                <div class="header pb-0">
                    <h2><strong>Residents</strong> list</h2>
                    <div class="row mt-2">
                        <div class="col">
                            <ul class="nav nav-modal mt-3">
                                <li class="nav-item active">
                                    <a href="/admin/residents" class="nav-link @if($tab == '') active @endif">Active</a>
                                </li>
                                <li class="nav-item">
                                    <a href="/admin/residents/archive" class="nav-link @if($tab == 'archive') active @endif">Archive</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-4">
                            <button data-toggle="modal" data-target="#mod-resident" type="button" class="btn btn-primary btn-round float-right md-trigger">Add Resident</button>
                            <button data-toggle="modal" data-target="#mod-import-resident" type="button" class="btn btn-primary btn-round float-right md-trigger">Import Residents</button>
                        </div>
                    </div>
                </div>

                <div class="body">
                    <table class="table data_table residents">
                        <thead>
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 20%">Name</th>
                                <th style="width: 10%">Mobile</th>
                                <th style="width: 20%">Building</th>
                                <th style="width: 10%">Unit No.</th>
                                <th style="width: 10%">Resident Level</th>
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
                    url: '{{route("app.resident.list", $tab)}}',
                    method: 'POST'
                },
                columns: [
                    {data: 'id', name: 'id', orderable: true},
                    {data: 'name', name: 'name', orderable: true},
                    {data: 'mobile', name: 'mobile', orderable: false},
                    {data: 'building', name: 'building'},
                    {data: 'unit_no', name: 'unit_no', orderable: false},
                    {data: 'level', name: 'level'},
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

                            // Resident Levels
                            case 5:
                                @php $residentLevels = \App\Models\Role::ResidentLevels()->get(['id', 'display_name']); @endphp
                                
                                var choices = {!! json_encode($residentLevels) !!}
                                var select = $('<select>').addClass('form-control').appendTo($(td))

                                select.append($("<option>").attr('value', '').text('All'))
                                $(choices).each(function() {
                                    select.append($("<option>").attr('value', this.id).text(this.display_name))
                                })
                                select.on('change', function() {
                                    column.search($(this).val()).draw()
                                })
                                select.val('').change()
                                break;

                            // Status
                            case 6:
                                var choices = [
                                    {val : '', text: 'All'},
                                    {val : {{\App\Models\User::$STATUS_ACTIVE}}, text: 'Active'},
                                    {val : {{\App\Models\User::$STATUS_INVITED}}, text: 'Invited'},
                                    {val : {{\App\Models\User::$STATUS_FLAGGED}}, text: 'Flagged'},
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
    @include('app.residents.modals.add')
    @include('app.residents.modals.invite')
    @include('app.residents.modals.import')
@endsection
