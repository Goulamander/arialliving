@extends('layouts.dashboard')

@section('title', 'Settings '.$SCR_TITLE)


@section('content')
    @include('layouts.messagesTemplate')

        <div class="panel">

            <div class="panel-header">

                <h3>Settings</h3>

                <div class="row">
                    <div class="col-12">

                        <ul id="SettingsGroupNav" class="nav nav-modal fade-menu pl-0" role="tablist">
                            
                            <li class="nav-item">
                                <a class="nav-link active show" data-toggle="tab" data-href="setting-categories" href="#setting-categories-tab" role="tab">Categories</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" data-href="setting-residemail-templates" href="#setting-residemail-templates-tab" role="tab">Resident Email templates</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" data-href="setting-adminemail-templates" href="#setting-adminemail-templates-tab" role="tab">Admin Email templates</a>
                            </li>
                        </ul>

                    </div>
                </div>
            </div>

            <div class="panel-body">

                <div class="tab-content">
                
                    <div class="tab-pane active show" id="setting-categories-tab" role="tabpanel">
                        <div class="card mt-4">
                            <div class="header pb-0">
                                <h2><strong>Categories</strong></h2>
                                <div class="row mt-2">
                                    <div class="col">
                                        <button type="button" data-toggle="modal" data-target="#mod-category" class="btn btn-primary btn-round float-right md-trigger">Add Category</button>
                                    </div>
                                </div>
                            </div>
                            <div class="body">

                                <table class="data_table table categories">
                                    <thead>
                                        <tr>
                                            <th style="width: 25%">Name</th>
                                            <th style="width: 35%">Calendar Colour</th>
                                            <th style="width: 15%">No. of Items</th>
                                            <th style="width: 15%">Status</th>
                                            <th style="width: 10%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="categoryList"></tbody>
                                </table>
                         
                            </div>
                        </div>

                    </div>

                    <div class="tab-pane" id="setting-residemail-templates-tab" role="tabpanel">
                        <div class="card mt-4">
                            <div class="header pb-0">
                                <h2><strong>Resident Email templates</strong></h2>
                            </div>
                            <div class="body">
                                <form action="{{route('app.settings.emailTemplates.update')}}" method="POST" autocomplete="off" data-store="true">
                         
                                    @if($template_groups)
                                        <ul class="nav nav-modal mb-3" role="tablist">
                                        @php $i = 0; @endphp
                                        @foreach($template_groups as $group_key => $templates)
                                            @php $key = str_slug($group_key); @endphp
                                            <li class="nav-item"> 
                                                <a class="nav-link @if($i == 0) active @endif" id="template_{{$key}}-tab" data-toggle="pill" href="#template_{{$key}}" role="tab" aria-selected="true">{{$group_key}}</a> 
                                            </li>
                                            @php $i++; @endphp
                                        @endforeach
                                        </ul>
                                        <div class="tab-content">
                                        @php $i = 0; @endphp
                                        @foreach($template_groups as $group_key => $templates)
                                            @php $key = str_slug($group_key); @endphp

                                            <div id="template_{{$key}}" class="tab-pane fade @if($i == 0) show active @endif" role="tabpanel" aria-labelledby="template__{{$key}}">
                                               
                                                <div class="row mt-5">
                                                    <div class="col-xl-8">

                                                        <div class="row">
                                                            <div class="col">
                                                                <h3 class="">{{$group_key}}</h3>
                                                                @if ($group_key == 'Booking Reminder')
                                                                    <p>This booking reminder is sent to Residents x-hrs before their booking.</p>
                                                                @endif
                                                            </div>
                                                            <div class="col-3">
                                                                <button type="submit" class="btn btn-primary btn-round float-right">Save template</button>
                                                            </div>
                                                        </div>

                                                        @foreach($templates as $email_template)
                                                        <div class="mb-4 email_template_card">

                                                            <h4>{{$email_template->label}}</h4>

                                                            @if($email_template->replace != '')
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <small class="text-muted">Shortcodes</small>
                                                                    @foreach(json_decode($email_template->replace) as $code) 
                                                                        <span class="label l-gray">{{$code->html}}</span> 
                                                                    @endforeach 
                                                                </div>
                                                            </div>
                                                            @endif
                                                            
                                                            @if($email_template->type == 'textarea')
                                                            <div class="_html_editor _html_content" data-name="{{$email_template->code}}" name="{{str_replace('.', '__', $email_template->code)}}">{!! $email_template->value !!}</div>
                                                            @else 
                                                            <input type="text" class="form-control" value="{!! $email_template->value !!}" name="{{$email_template->code}}">
                                                            @endif
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                            </div>
                                            @php $i++; @endphp
                                        @endforeach
                                        </div>
                                        
                                    @endif
                               
                                </form>
                            </div>
                        </div>

                    </div>
                    <div class="tab-pane" id="setting-adminemail-templates-tab" role="tabpanel">
                        <div class="card mt-4">
                            <div class="header pb-0">
                                <h2><strong>Admin Email templates</strong></h2>
                            </div>
                            <div class="body">
                                <form action="{{route('app.settings.emailTemplates.update')}}" method="POST" autocomplete="off" data-store="true">
                         
                                    @if($admin_template_groups)
                                        <ul class="nav nav-modal mb-3" role="tablist">
                                        @php $i = 0; @endphp
                                        @foreach($admin_template_groups as $group_key => $templates)
                                            @php $key = str_slug($group_key); @endphp
                                            <li class="nav-item"> 
                                                <a class="nav-link @if($i == 0) active @endif" id="template_{{$key}}-tab" data-toggle="pill" href="#template_{{$key}}" role="tab" aria-selected="true">{{$group_key}}</a> 
                                            </li>
                                            @php $i++; @endphp
                                        @endforeach
                                        </ul>
                                        <div class="tab-content">
                                        @php $i = 0; @endphp
                                        @foreach($admin_template_groups as $group_key => $templates)
                                            @php $key = str_slug($group_key); @endphp

                                            <div id="template_{{$key}}" class="tab-pane fade @if($i == 0) show active @endif" role="tabpanel" aria-labelledby="template__{{$key}}">
                                               
                                                <div class="row mt-5">
                                                    <div class="col-xl-8">

                                                        <div class="row">
                                                            <div class="col">
                                                                <h3 class="">{{$group_key}}</h3>
                                                                @if ($group_key == 'Booking Reminder')
                                                                    <p>This booking reminder is sent to Residents x-hrs before their booking.</p>
                                                                @endif
                                                            </div>
                                                            <div class="col-3">
                                                                <button type="submit" class="btn btn-primary btn-round float-right">Save template</button>
                                                            </div>
                                                        </div>

                                                        @foreach($templates as $email_template)
                                                        <div class="mb-4 email_template_card">

                                                            <h4>{{$email_template->label}}</h4>

                                                            @if($email_template->replace != '')
                                                            <div class="row">
                                                                <div class="col-12">
                                                                    <small class="text-muted">Shortcodes</small>
                                                                    @foreach(json_decode($email_template->replace) as $code) 
                                                                        <span class="label l-gray">{{$code->html}}</span> 
                                                                    @endforeach 
                                                                </div>
                                                            </div>
                                                            @endif
                                                            
                                                            @if($email_template->type == 'textarea')
                                                            <div class="_html_editor _html_content" data-name="{{$email_template->code}}" name="{{str_replace('.', '__', $email_template->code)}}">{!! $email_template->value !!}</div>
                                                            @else 
                                                            <input type="text" class="form-control" value="{!! $email_template->value !!}" name="{{$email_template->code}}">
                                                            @endif
                                                        </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                            </div>
                                            @php $i++; @endphp
                                        @endforeach
                                        </div>
                                        
                                    @endif
                               
                                </form>
                            </div>
                        </div>
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
                buttons: [],
                iDisplayLength: 100,
                dom: '<>tip',
                ajax: {
                    url: '{{ route("app.settings.category.list") }}',
                    method: 'POST'
                },
                columns: [
                    {data: 'name', name: 'name', orderable: false},
                    {data: 'color', name: 'color', orderable: false},
                    {data: 'items_no', name: 'items_no', orderable: false},
                    {data: 'status', name: 'status', orderable: false},
                    {data: 'actions', name: 'actions', orderable: false}
                ],
                initComplete: function () {
                    //
                    this.addClass('ready');
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
        @include('app.settings.modals.addCategory')
    @endif
@endsection

