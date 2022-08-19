@extends('layouts.auth')
@section('title', 'Login | '.config('app.name') )

@section('content')
<div class="col-md-12 content-center">
    <h4 class="logo">
        <img src="{{asset('/img/aria_logo_w.png')}}" alt="Aria Living">
    </h4>
    <div class="row clearfix d-flex mt-l">
        <div class="col-lg-6 col-md-12 content_div">
            @include('auth.partials.content')
        </div> 
        <div class="col-lg-5 col-md-12 offset-lg-1 card_div">
            <div class="card-plain">
                <div class="header">
                    <h5>Log in</h5>
                </div>
                <form class="form" method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" autocomplete="email" placeholder="Email" autofocus required>
                    </div>
                    <div class="form-group">
                        <div class="input-group">
                            <input type="password" name="password" class="form-control" autocomplete="password" placeholder="Password" required>
                            <span class="input-group-addon"><i class="zmdi zmdi-lock"></i></span>
                        </div>
                    </div>
                    <div class="form-group text-left">
                        <div class="checkbox">
                            <input type="checkbox" name="remember" id="remember_me" {{ old('remember') ? 'checked' : '' }}>
                            <label for="remember_me">{{ __('Remember Me') }}</label>
                        </div>
                    </div>   
                    @include('auth.partials.response')
                    <div class="footer mt-20">
                        <button type="submit" class="btn btn-primary btn-round btn-block">
                            {{ __('SIGN IN') }}
                        </button>
                    </div>         
                </form>
                @if (Route::has('password.request'))
                    <a class="link" href="{{ route('password.request') }}">
                        {{ __('Forgot Your Password?') }}
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>
@stop