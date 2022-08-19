@extends('layouts.dashboard')

@section('title', $user->fullName().' - '.$user->role->display_name.' | '.config('app.name'))


@section('content')
@include('layouts.messagesTemplate')


<div class="panel">
    <div class="panel-header"></div>
    <div class="panel-body">

        <div class="row">
            <div class="col-sm-12 col-lg-6">
                <div class="card">
                    <div class="header">
                        <h2>
                            <strong>Admin Profile</strong>
                        </h2>
                    </div>
                    <div class="body">
                        <small>Name</small>
                        <h4 class="mt-0">{{$user->fullName()}} <span class="label m-0">{{$user->role->display_name}}</span> {!!$user->getStatus()!!}</h4>
                        <form method="POST" autocomplete="off" action="{{route('app.user.update', $user->id)}}">
                            @csrf
                            <input name="_method" type="hidden" value="PUT">
                            @php $fields = json_decode(json_encode(\App\Models\User::user_form_fields())); @endphp

                            @foreach($fields as $key => $field)
                                {!! App\Helpers\FormHelper::getFields($key, $field, $user) !!}
                            @endforeach
                            @if($can_edit)
                            <div class="clearfix"></div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" name="store" class="btn btn-sm btn-primary float-right">Save changes</button>
                                </div>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>

                @if(!$user->isSuperAdmin())
                {{-- Building Access --}}
                <div class="card">
                    <div class="header">
                        <h2>
                            <strong>Buildings</strong>
                            <small>Admin has {{$user->role->display_name}} access to</small>
                        </h2>
                    </div>
                    <div class="body">
                        <form method="POST" action="{{route('app.user.updateBuildings', $user->id)}}" autocomplete="off">
                            @csrf
                            <input name="_method" type="hidden" value="PUT">
                            <select class="form-control" data-s2="1" data-source="building" name="buildings" data-return="id" data-placeholder="Tap here to find buildings" multiple="" @if(!$can_edit) disabled @endif</select>
                            @if($user->buildings)
                                @foreach($user->buildings as $building)
                                    <option value="{{$building->id}}" data-selected="{{ base64_encode(json_encode(['name' => $building->name, 'postcode' => $building->postcode, 'suburb' => $building->suburb])) }}" selected></option>
                                @endforeach
                            @endif
                            </select>
                            @if($can_edit)
                            <div class="clearfix"></div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" name="store" class="btn btn-sm btn-primary float-right mt-3">Save changes</button>
                                </div>
                            </div>
                            @endif
                        </form>
                    </div>
                </div>
                @endif

                {{-- Settings --}}
                <div class="card">
                    <div class="header">
                        <h2>
                            <strong>Settings</strong>
                            <small>Settings your account.</small>
                        </h2>
                    </div>
                    <div class="body">
                        <form method="POST" class="jsSubmit" autocomplete="off" action="{{route('app.user.storeNotiSetting', $user->id)}}">
                            {{-- Notifications --}}
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="custom-control custom-switch">
                                        @php 

                                        $additional_password_prompt_checked = 'checked';

                                        if($user->settings && $user->settings->additional_password_prompt == 0) {
                                            $additional_password_prompt_checked = '';
                                        }

                                        @endphp
                                        <input type="checkbox" class="custom-control-input" name="additional_password_prompt" id="additional_password_prompt" {{$additional_password_prompt_checked}}>
                                        <label class="custom-control-label" for="additional_password_prompt">Additional Password Prompt</label>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    
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


                {{-- Notification options --}}
                <div class="card">
                    <div class="header">
                        <h2>
                            <strong>User Notifications</strong>
                            <small>Choose how you get notified when a resident makes or changes an online booking.</small>
                        </h2>
                    </div>
                    <div class="body">
                        <form method="POST" autocomplete="off" action="{{route('app.user.storeNotiSetting', $user->id)}}">
                            {{-- Notifications --}}
                            <div class="row">
                                <div class="col-sm-6">
                                    <strong class="d-block mb-4">Send me an Email when...</strong>
                                    @php 

                                    $selected_options = [];

                                    if($user->settings && $user->settings->notifications_email) {
                                        $selected_options = explode(',', $user->settings->notifications_email);
                                    }

                                    @endphp

                                    @foreach($notification_options as $key => $opt)
                                    <div class="checkbox">
                                        @php $checked = in_array($key, $selected_options) ? ' checked' : '' @endphp
                                        <input type="checkbox" value="{{$key}}" id="e_{{$key}}" name="email_notification"{{$checked}}>
                                        <label for="e_{{$key}}">{{$opt}}</label>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="col-sm-6">
                                    <strong class="d-block mb-4">Send me an SMS when...</strong>
                                    @php 

                                    $selected_options = [];

                                    if($user->settings && $user->settings->notifications_sms) {
                                        $selected_options = explode(',', $user->settings->notifications_sms);
                                    }

                                    @endphp
                                    @foreach($notification_options as $key => $opt)
                                    <div class="checkbox">
                                        @php $checked = in_array($key, $selected_options) ? ' checked' : '' @endphp
                                        <input type="checkbox" value="{{$key}}" id="s_{{$key}}" name="sms_notification"{{$checked}}>
                                        <label for="s_{{$key}}">{{$opt}}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @if($can_edit)
                            <div class="row">
                                <div class="col-sm-12">
                                    <button type="submit" name="store" class="btn btn-sm btn-primary float-right mt-4">Save changes</button>
                                </div>
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

@endsection