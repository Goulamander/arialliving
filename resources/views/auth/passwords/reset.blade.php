@extends('layouts.auth')
@section('title', 'Set your password | '.config('app.name') )

@section('content')
<div class="col-md-12 content-center">
    <h4 class="logo">
        <img src="{{asset('/img/aria_logo_w.png')}}" alt="Aria Living">
    </h4>
    <div class="row clearfix mt-l">
        <div class="col-lg-6 col-md-12 content_div">
            @include('auth.partials.content')
        </div> 
        <div class="col-lg-5 col-md-12 offset-lg-1 card_div">
            <div class="card-plain">
                <div class="header">
                    <h5>{{ __('Set your new password') }}</h5>
                </div>
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{$token}}">
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="email" placeholder="Email" autofocus required>
                    </div>
                    <div class="form-group">
                        <input type="password" id="new_password" name="password" class="form-control" placeholder="Password" 
                            data-parsley-minlength="8"
                            data-parsley-number="1"
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="form-group">
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" 
                            data-parsley-equalto="#new_password"
                            required
                            autocomplete="off"
                        >
                    </div>
                    @include('auth.partials.response')
                    <div class="footer">
                        <button type="submit" class="btn btn-primary btn-round btn-block">
                            {{ __('Reset Password') }}
                        </button>
                    </div>         
                </form>
                @if (Route::has('password.request'))
                    <a class="link" href="{{ route('login') }}">
                        {{ __('Log in') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@stop