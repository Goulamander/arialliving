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
                <div class="header acc-setup-header">
                    <h5>Hey {{ $user->first_name }} 👋</h5>
                    <h3>welcome to Aria Living</h3>
                    <span>set your password</span>
                </div>
                <form class="form" method="POST" action="{{ route('user.activate.setpassword', $token) }}">
                    @csrf
                    <div class="form-group">
                        <input type="password" id="Password" name="password" class="form-control" placeholder="Password" 
                            data-parsley-minlength="8"
                            data-parsley-number="1"
                            required
                            autocomplete="off"
                        >
                    </div>
                    <div class="form-group">
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password" minLength="8" data-parsley-equalto="#Password" required>
                    </div>
                    @include('auth.partials.response')
                    <div class="footer">
                        <button type="submit" class="btn btn-primary btn-round btn-block">
                            {{ __('Save & Log in') }}
                        </button>
                    </div>         
                </form>
            </div>
        </div>
    </div>
</div>
@stop