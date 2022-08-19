@extends('layouts.dashboard')

@section('title', 'Profile | '.config('app.name'))

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
                            <strong>Profile</strong>
                        </h2>
                    </div>
                    <div class="body">
                        <small>Name</small>
                        <h4 class="mt-0">{{$user->fullName()}} <span class="label">{{$user->role->display_name}}</span></h4>
                        <form method="POST" autocomplete="off" action="{{route('resident.profile.storeContact')}}">
                            @csrf
                            <strong class="d-block mb-4 mt-5">Your Contact details</strong>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Email Address</label>
                                        <input type="email" class="form-control" name="email" value="{{$user->email}}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="control-label">Mobile</label>
                                        <input type="text" class="form-control mobile-number" name="mobile" value="{{$user->mobile}}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="submit" name="store" class="btn btn-sm btn-primary float-right">Save changes</button>
                                </div>
                            </div>
                        </form>
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
                        <form method="POST" class="jsSubmit" autocomplete="off" action="{{route('resident.profile.storeUserSetting')}}">
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

                <div class="card">
                    <div class="header">
                        <h2>
                            <strong>Credit Card</strong>
                        </h2>
                    </div>
                    <div class="body">
                        <form method="POST" autocomplete="off" data-reload="true" action="{{route('resident.profile.storeCreditCard')}}" data-encrypt="true">
                            {{-- Credit Card --}}
                            @include('resident.item.partials._creditCard', ['is_submit_btn' => true])
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="header">
                        <h2>
                            <strong>App Security</strong>
                        </h2>
                    </div>
                    <div class="body pt-0">
                        {{-- Change password --}}
                        @include('app._partials.changePassword', ['change_password_route' => route('resident.profile.storePassword')])
                        {{-- 2FA --}}
                        @include('app._partials.2faSetup', ['setup_route' => ''])
                    </div>
                </div>

            </div>
        </div>

        


    </div>
</div>
@endsection


@section('scripts')
<script src="{{ asset('/js/eWay.js')}}"></script>
@endsection