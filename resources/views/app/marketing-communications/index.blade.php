@extends('layouts.dashboard')

@section('title', 'Marketing Communications | '.config('app.name'))


@section('content')
    @include('layouts.messagesTemplate')

    <style>
        ._full_html_editor .ql-editor{
            min-height: 300px;
        }
    </style>

    <div class="row clearfix">
        <div class="col-lg-12">
            <div class="card">

                <div class="header pb-0">
                    <h2><strong>Marketing Communications</strong> list</h2>
                    <div class="row mt-2">
                        <div class="col-sm-8">
                            <ul class="nav nav-modal mt-3">
                                <li class="nav-item active">
                                    <a href="/admin/marketing-communications" class="nav-link @if($tab == '') active @endif">Active</a>
                                </li>
                                <li class="nav-item">
                                    <a href="/admin/marketing-communications/archive" class="nav-link @if($tab == 'archive') active @endif">Archive</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-sm-4">
                            <div class="btn-hspace">
                                <button type="button" data-toggle="dropdown" class="btn btn-primary btn-arrow btn-round float-right btn-i" aria-expanded="true">
                                    Add New <i class="material-icons">expand_more</i>
                                </button>
                                <ul role="menu" class="dropdown-menu dropdown-menu-right">
                                    <li><button type="button" data-toggle="modal" data-target="#mod-resident-level" class="actions md-trigger">Resident Level</button></li>
                                    <li><button type="button" data-toggle="modal" data-target="#mod-buildings" class="actions md-trigger">Buildings</button></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="body">
                    <table class="table data_table residents">
                        <thead>
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 20%">Subject</th>
                                <th style="width: 10%">Send Via</th>
                                <th style="width: 20%">Status</th>
                                <th style="width: 10%">Created At</th>
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
         * On select change
         */
        $(document).on('change', 'select[name=resident_levels], select[name=building_id]', function(e)  { // Validate confirm password form
            e.preventDefault();
            $.ajax({
                url: `{{route("app.marketing-communications.getResidentList")}}?${this.name}=${this.value}`,
                type: "GET",
                success: function (res) {
                    if(res.data && res.data.length > 0){
                        let options = '';
                        res.data.map(v => {
                            options += `<option value="${v.id}">${v.first_name} ${v.last_name}</option>`;
                        })
                        $('select[name=receiver').html(options);
                    }
                },
                fail: function (e) {
                    _errorResponse(e)
                }
            });
        });

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
                    url: '{{route("app.marketing-communications.list", $tab)}}',
                    method: 'POST'
                },
                columns: [
                    {data: 'id', name: 'id', orderable: true},
                    {data: 'subject', name: 'subject', orderable: true},
                    {data: 'send_via', name: 'send_via'},
                    {data: 'status', name: 'status'},
                    {data: 'created_at', name: 'created_at'},
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
                },
                order: [[ 0, "desc" ]],
            })
        }
        // 
        window.dataTable = init_DataTable()
    </script>
@endsection


@section('modals')
    @include('app.marketing-communications.modals.add-resident-level')
    @include('app.marketing-communications.modals.add-buildings')
@endsection
