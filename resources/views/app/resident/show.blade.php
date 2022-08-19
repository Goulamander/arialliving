@extends('layouts.dashboard')

@section('title', $resident->fullName().' - Profile | '.config('app.name'))

@section('content')
@include('layouts.messagesTemplate')

<div class="panel">
    <div class="panel-header"></div>
    <div class="panel-body">

        <div class="row">
            <div class="col-sm-12 col-lg-4">
                <div class="card">
                    <div class="header">
                        <h2 class="float-left">
                            <strong>Resident Profile</strong>
                        </h2>
                        <ul class="header-dropdown">
                            <li class="dropdown"> <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"> <i class="zmdi zmdi-more"></i> </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    {{-- edit resident --}}
                                    <li>
                                        <a href="#" data-toggle="modal" data-target="#mod-resident" class="md-trigger">Edit</a>
                                    </li>
                                    {{-- invite --}}
                                    @if( $resident->canBeInvited() )
                                    <li>
                                        <a class="actions" data-target="#mod-invite"  href="{{route('app.user.invite', $resident->id)}}">Invite</a>
                                    </li>
                                    @endif
                                    {{-- Flag resident --}}
                                    <li>
                                        <button class="no-btn" data-toggle="modal" data-target="#mod-flag-resident">@if($resident->is_flagged) Remove Flag @else Flag Resident @endif</button>
                                    </li>       
                                    @if( Auth::user()->canDelete() && !$resident->trashed() )
                                    <li>
                                        <a href="{{route('app.user.delete', $resident->id)}}" class="actions" data-target="#mod-delete" data-reload="true">Delete Resident</a>
                                    </li>
                                    @endif
                                </ul>
                            </li>
                        </ul>
                    </div>
                    <div class="body">

                        <div class="row">
                            <div class="col-12 mb-3 profile-head">
                                <h3 class="mb-0">{{$resident->fullName()}} {!!$resident->getStatus()!!}</h3>
                            </div>
                        </div>
                  
                        <h4>Residency</h4>
                        
                        <div class="row mb-3">
                            <div class="col-sm-7">
                                <small class="text-muted">Building</small>
                                @if(!$resident->building->isEmpty())
                                <p><a href="{{route('app.building.show', $resident->building[0]->id)}}">{{$resident->building[0]->name}}</a></p>
                                @endif
                            </div>
                            <div class="col-sm-5">
                                <small class="text-muted">Resident Level</small>
                                <p><strong>{{$resident->role->display_name}}</strong></p>
                            </div>
                        </div>
                 
                        <div class="row">
                            <div class="col-sm-3">
                                <small class="text-muted">Unit No.</small>
                                @if(!$resident->building->isEmpty())
                                <p>{{$resident->building[0]->pivot->unit_no}}</p>
                                @else
                                <p>-</p>
                                @endif
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted">From</small>
                                @if(!$resident->building->isEmpty())
                                <p>{{$resident->building[0]->pivot->relation_start ? dateFormat($resident->building[0]->pivot->relation_start) : '-'}}</p>
                                @else
                                <p>-</p>
                                @endif
                            </div>
                            <div class="col-sm-4">
                                <small class="text-muted">To</small>
                                @if(!$resident->building->isEmpty())
                                <p>{{$resident->building[0]->pivot->relation_end ? dateFormat($resident->building[0]->pivot->relation_end) : '-'}}</p>
                                @else
                                <p>-</p>
                                @endif
                            </div>
                        </div>

                 
                        <h4>Contact details</h4>

                        <div class="row">
                            <div class="col-7">
                                <small class="text-muted">Email address</small>
                                <p><a href="mailto:{{$resident->email}}">{{$resident->email}}</a></p>
                            </div>
                            <div class="col-5">
                                <small class="text-muted">Mobile</small>
                                <p><a href="tel:{{$resident->mobile}}">{{$resident->mobile}}</a></p>
                            </div>
                        </div>

                        @if(!$resident->building->isEmpty() && $resident->building[0]->pivot->notes)
                        <hr>
                        <h4>Notes</h4>
                        <p>{{$resident->building[0]->pivot->notes}}</p>
                        @endif


                    </div>
                </div>

                {{-- Settings --}}
                <div class="card">
                    <div class="header">
                        <h2>
                            <strong>Settings</strong>
                            <small>Settings your account.</small>
                        </h2>
                    </div>
                    <div class="body">
                        <form method="POST" class="" autocomplete="off" action="{{route('app.resident.profile.storeUserSetting', $resident->id)}}">
                            {{-- Notifications --}}
                            <div class="row">
                                <div class="col-12">
                                    <div class="custom-control custom-switch">
                                        @php 

                                        $additional_password_prompt_checked = 'checked';

                                        if($resident->settings && $resident->settings->additional_password_prompt == 0) {
                                            $additional_password_prompt_checked = '';
                                        }

                                        @endphp
                                        <input type="checkbox" class="custom-control-input" name="additional_password_prompt" id="additional_password_prompt" {{$additional_password_prompt_checked}}>
                                        <label class="custom-control-label" for="additional_password_prompt">Additional Password Prompt</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <button type="submit" name="store" class="btn btn-sm btn-primary float-right mt-4">Save changes</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="body">

                        <ul class="nav nav-modal mb-3" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="active-bookings-tab" data-toggle="pill" href="#active-bookings" role="tab" aria-controls="pills-home" aria-selected="true">Active Bookings</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="archive-bookings-tab" data-toggle="pill" href="#archive-bookings" role="tab" aria-controls="pills-profile" aria-selected="false">Booking Archive</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="admin-comments-tab" data-toggle="pill" href="#admin-comments" role="tab" aria-controls="pills-contact" aria-selected="false">Admin Comments</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="residence-history-tab" data-toggle="pill" href="#residence-history" role="tab" aria-controls="pills-contact" aria-selected="false">Residence History</a>
                            </li>
                        </ul> 

                        <div class="tab-content">

                            <div id="active-bookings" class="tab-pane fade show active" role="tabpanel" aria-labelledby="active-bookings-tab">  
                                <h4>Active Bookings</h4>
                                <table class="table_active_bookings data_table bookings">
                                    <thead>
                                        <tr>
                                            <th style="width: 10%">ID</th>
                                            <th style="width: 30%">Title</th>
                                            <th style="width: 30%">Dates</th>
                                            <th style="width: 20%">Total</th>
                                            <th style="width: 10%">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div id="archive-bookings" class="tab-pane fade" role="tabpanel" aria-labelledby="archive-bookings-tab">
                                <h4>Archive Bookings</h4>
                                <table class="table_archive_bookings data_table bookings">
                                    <thead>
                                        <tr>
                                            <th style="width: 10%">ID</th>
                                            <th style="width: 30%">Title</th>
                                            <th style="width: 30%">Dates</th>
                                            <th style="width: 20%">Total</th>
                                            <th style="width: 10%">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <div id="admin-comments" class="tab-pane fade" role="tabpanel" aria-labelledby="admin-comments-tab">
                                <h4>Admin Comments</h4>
                                @include('app._partials.comments', ['comments' => $resident->comments])
                            </div>

                            <div id="residence-history" class="tab-pane fade" role="tabpanel" aria-labelledby="residence-history-tab">
                                <h4>Residence history</h4>
                                <table class="data_table ready">
                                    <thead>
                                        <tr>
                                            <th>Building</th>
                                            <th>Unit no.</th>
                                            <th>Residency Start</th>
                                            <th>Residency End</th>
                                            <th>Residency Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    @if($resident->allBuildings->isEmpty())
                                        <tr>
                                            <td colspan="5">No Residence history yet</td>
                                        </tr>
                                    @else 
                                        @foreach($resident->allBuildings as $residency)
                                        <tr>
                                            <td>
                                                <a href="{{route('app.building.show', $residency->id)}}" class="row-col title">
                                                    {!! $residency->ThumbOrInitials() !!}
                                                    <span>{{$residency->name}}</span>
                                                    <small data-exclude="true">{{$residency->suburb}}</small>
                                                </a>
                                            </td>
                                            <td>{{$residency->pivot->unit_no}}</td>
                                            <td>{{dateFormat($residency->pivot->relation_start)}}</td>
                                            <td>{{dateFormat($residency->pivot->relation_end)}}</td>
                                            <td>{!!$residency->getResidencyStatus()!!}</td>
                                            <td><small>{{$residency->pivot->notes}}</small></td>
                                        </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                </table>
                            </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>


@endsection


@section('scripts')
<script type="text/javascript">

/** init dataTables */
function init_active_DataTable() {

    return $('.table_active_bookings').dataTable({
        buttons: ['csv'],
        iDisplayLength: 100,
        dom: '<"export_buttons"B>tip',
        ajax: {
            method: 'POST',
            url: '{{ route("app.booking.list", "active") }}',
            data: {
                user_id: {{$resident->id}}
            }
        },
        columns: [
            {data: 'id', name: 'id'},
            {data: 'title', name: 'title'},
            {data: 'date_time', name: 'date_time'},
            {data: 'total', name: 'total'},
            {data: 'status', name: 'status'}
        ],
        initComplete: function () {
            this.addClass('ready');
        },
        order: [[ 0, "desc" ]],
    })
}

function init_archive_DataTable() {

    return $('.table_archive_bookings').dataTable({
        buttons: ['csv'],
        iDisplayLength: 100,
        dom: '<"export_buttons"B>tip',
        ajax: {
            method: 'POST',
            url: '{{ route("app.booking.list", "archive") }}',
            data: {
                user_id: {{$resident->id}}
            }
        },
        columns: [
            {data: 'id', name: 'id'},
            {data: 'title', name: 'title'},
            {data: 'date_time', name: 'date_time'},
            {data: 'total', name: 'total'},
            {data: 'status', name: 'status'}
        ],
        initComplete: function () {
            this.addClass('ready');
        },
        order: [[ 0, "desc" ]],
    })
}

window.dataTableActive  = init_active_DataTable()
window.dataTableArchive = init_archive_DataTable()

</script>
@endsection


@section('modals')
    {{-- Edit --}}
    @include('app.residents.modals.edit')
    {{-- Invite --}}
    @include('app.residents.modals.invite')
    {{-- Invite --}}
    @include('app.residents.modals.flag')
    {{-- Delete --}}
    @include('app._partials.modals.confirmDelete')
@endsection