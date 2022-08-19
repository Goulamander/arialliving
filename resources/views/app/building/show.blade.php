@extends('layouts.dashboard')

@section('title', $building->name.' - Buildings | '.config('app.name'))

@section('content')
    @include('layouts.messagesTemplate')
    <div class="row clearfix with_banner">
        @php 

        $style = '';
        if(isset($building->images[array_key_first($building->images)])) {
            $style = 'style="background-image: url('.\Storage::url($building->images[array_key_first($building->images)]).')"';
        }

        @endphp 
        <div class="single_top_banner" {!! $style !!}>
            <div class="container">
                <button type="button" data-toggle="modal" data-target="#mod-edit-gallery" class="edit">Edit Gallery</button>
            </div>
        </div>

        {{-- Side Card --}}
        <div class="col-lg-4 col-md-12">
            <div class="card">

                <div class="header">
                    <h2><strong>Building Profile</strong></h2>
                    <ul class="header-dropdown">
                        <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more"></i> </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a href="#" data-toggle="modal" data-target="#mod-building" class="md-trigger">Edit</a></li>
                                @if( ! $building->trashed() )
                                <li><a href="{{route('app.building.delete', $building->id)}}" class="actions" data-target="#mod-delete">Delete Building</a></li>
                                @endif
                            </ul>
                        </li>
                    </ul>
                </div>

                <div class="body">

                    <div class="row">
                        <div class="col-12 profile-head">
                            <div class="image-thumb-uploader" data-name="file" data-path="{{$building->imagePath()}}" data-filename="@if($building->is_thumb){{$building->is_thumb}}@endif" data-process-type="thumbnail">
                                @if( $building->is_thumb ) 
                                <input type="hidden" data-type="local" value="{{ encrypt($building->getThumbWithoutDomain()) }}">
                                @endif
                            </div>
                            <h3 class="mb-0">{{$building->name}}</h3>
                            <span class="text-light">{{$building->fullAddress()}}</span>
                        </div>
                    </div>

                    <h4>Staff</h4>
                    @if(!$building_staff->isEmpty())
                        @foreach($building_staff as $staff)

                            <div class="building-staff">

                                <h4>{{$staff->name}}<span> - {{$staff->role}}</span></h4> 
                                
                                @if($staff->mobile)<a href="tel:{{$staff->mobile}}" class="phone">{{$staff->mobile}}</a>@endif
                                @if($staff->email)<a href="mailto:{{$staff->email}}" class="email">{{$staff->email}}</a>@endif
                            </div>

                        @endforeach
                    @else
                    <small>No managers or staff assigned</small>
                    @endif

                    <h4>Onsite Contact</h4>
                    <small class="text-muted">Name: </small>
                    <p>{{$building->contact_name}}</p>
                    <hr>
                    <small class="text-muted">Email address: </small>
                    <p><a href="mailto:{{$building->email}}">{{$building->email}}</a></p>
                    <hr>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted">Mobile: </small>
                            <p><a href="tel:{{$building->mobile}}">{{$building->mobile}}</a></p>
                        </div>
                        <div class="col-6">
                            <small class="text-muted">Phone: </small>
                            <p><a href="tel:{{$building->phone}}">{{$building->phone}}</a></p>
                        </div>
                    </div>
                    <h4>Office Hours</h4>
                    @if($building->office_hours)
                    <ul class="opening-hours">
                        @foreach($building->office_hours as $day => $values)
                        <li>
                            <span>{{$day}}</span>
                            <small>
                            @if($values['status'] == 1) 
                                {{$values['from']}} - {{$values['to']}}
                            @else
                                Closed
                            @endif
                            </small>
                        </li>
                        @endforeach
                    </ul>    
                    @endif
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="body">
                    <ul class="nav nav-modal mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="residents-tab" data-toggle="pill" href="#residents" role="tab" aria-selected="true">Residents</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="building-page-tab" data-toggle="pill" href="#building-page" role="tab" aria-selected="false">Building Page</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="admin-comments-tab" data-toggle="pill" href="#admin-comments" role="tab" aria-selected="false">Admin Comments</a>
                        </li>
                    </ul> 
                    <div class="tab-content">

                        <div id="residents" class="tab-pane fade show active" role="tabpanel" aria-labelledby="residents-tab">  
                            <h4>Residents</h4>
                            <table class="table data_table residents">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">ID</th>
                                        <th style="width: 25%">Name</th>
                                        <th style="width: 10%">Unit No.</th>
                                        <th style="width: 10%">Resident Level</th>
                                        <th style="width: 10%">Status</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>

                        <div id="building-page" class="tab-pane fade" role="tabpanel" aria-labelledby="building-page-tab">
                            <form method="POST" action="{{route('app.building.store.content', $building->id)}}" autocomplete="off">
                                {{ csrf_field() }}
                                <div class="row">
                                    <div class="col-12">
                                        <h3 class="mt-4 float-left">Building Page</h3>
                                        <button type="submit" class="btn btn-primary btn-round btn-sm mt-4 float-right">Save Changes</button>
                                    </div>
                                    <div class="col-12">
                                        <div class="html_editor_wrap">
                                            <div data-name="building_content" class="_full_html_editor _html_content">{!! $building->page_content ? $building->page_content->content : ''!!}</div>
                                        </div> 
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div id="admin-comments" class="tab-pane fade" role="tabpanel" aria-labelledby="admin-comments-tab">
                            <h4>Admin Comments</h4>
                            @include('app._partials.comments', ['comments' => $building->comments])
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection


@section('modals')
    {{-- Edit Gallery --}}
    @include('app.item.modals.gallery', ['item' => $building])
    {{-- Edit building --}}
    @include('app.buildings.modals.edit')
    {{-- Add resident --}}
    @include('app.residents.modals.add')
    {{-- Delete --}}
    @include('app._partials.modals.confirmDelete')
@endsection


@section('scripts')
<script type="text/javascript">
    function init_DataTable() {

        return $('.table').dataTable({
            buttons: ['csv'],
            iDisplayLength: 100,
            dom: '<"export_buttons"B>tip',
            ajax: {
                method: 'POST',
                url: '{{ route("app.resident.list", $tab) }}',
                data: {
                    building_id: {{$building->id}}
                }
            },
            columns: [
                {data: 'id', name: 'id', orderable: true},
                {data: 'name', name: 'name', orderable: true},
                {data: 'unit_no', name: 'unit_no'},
                {data: 'level', name: 'level'},
                {data: 'status', name: 'status'}
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

                        // Resident Levels
                        case 3:
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
                        case 4:
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
                myapp.tippy.init()
            },
            order: [[ 0, "desc" ]],
        })
    }
    window.dataTable = init_DataTable()
</script>
@endsection