@extends('layouts.auth')
@section('title', 'Reset your password | '.config('app.name') )

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
                <div class="header text-left">
                    <h5 class="mb-2">{{ __('Forgot your password?') }}</h5>
                    <p>{{ __("Don't worry, we'll send you an email to reset your password.") }}</p>
                </div>
                <form class="form" method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="form-group">
                        <div class="input-group">
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="email" placeholder="Email" autofocus required>
                            <span class="input-group-addon"><i class="zmdi zmdi-account-circle"></i></span>
                        </div>
                    </div>
                    @include('auth.partials.response')
                    <div class="footer">
                        <button type="submit" class="btn btn-primary btn-round btn-block">
                            {{ __('Send Password Reset Link') }}
                        </button>
                    </div>         
                </form>
                <a class="link" href="{{ route('login') }}">
                    {{ __('Login') }}
                </a>
            </div>
        </div>
    </div>
</div>
@stop