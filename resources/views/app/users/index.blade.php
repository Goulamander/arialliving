@extends('layouts.dashboard')

@section('title', 'Users | '.config('app.name'))

@section('content')
    @include('layouts.messagesTemplate')

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">

                <div class="header pb-0">
                    <h2><strong>Users</strong> list</h2>
                    <div class="row mt-2">
                        <div class="col-sm-8">
                            <ul class="nav nav-modal mt-3">
                                <li class="nav-item active">
                                    <a href="/admin/users" class="nav-link @if($tab == '') active @endif">Active</a>
                                </li>
                                <li class="nav-item">
                                    <a href="/admin/users/archive" class="nav-link @if($tab == 'archive') active @endif">Archive</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-4">
                            <button data-toggle="modal" data-target="#mod-user" type="button" class="btn btn-primary btn-round float-right md-trigger">Add User</button>
                        </div>
                    </div>
                </div>

                <div class="body">
                    <table class="table data_table users">
                        <thead>
                            <tr>
                                <th style="width: 25%;">Name</th>
                                <th style="width: 10%;">Mobile</th>
                                <th style="width: 15%">Role</th>
                                <th style="width: 15%">Buildings Access</th>
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
                    url: '{{ route("app.user.list", $tab) }}',
                    method: 'POST'
                },
                columns: [
                    {data: 'name', name: 'name'},
                    {data: 'mobile', name: 'mobile', orderable: false},
                    {data: 'role', name: 'role'},
                    {data: 'building_access', name: 'building_access'},
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

                            // Role
                            case 2:
                                var choices = [
                                    {val : '', text: 'All'},
                                    {val : {{\App\Models\User::$ROLE_SUPER_ADMIN}}, text: 'Super Admin'},
                                    {val : {{\App\Models\User::$ROLE_ADMIN}}, text: 'Admin'},
                                    {val : {{\App\Models\User::$ROLE_BUILDING_MANAGER}}, text: 'Building Manager'},
                                    {val : {{\App\Models\User::$ROLE_STAFF}}, text: 'Staff'},
                                    {val : {{\App\Models\User::$ROLE_EXTERNAL}}, text: 'External Service Provider'},
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

                            // Buildings
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
                            case 4:
                                var choices = [
                                    {val : '', text: 'All'},
                                    {val : {{\App\Models\User::$STATUS_ACTIVE}}, text: 'Active'},
                                    {val : {{\App\Models\User::$STATUS_INVITED}}, text: 'Invited'},
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
    @include('app.users.modals.add')
    @include('app.users.modals.invite')
    @endif
@endsection