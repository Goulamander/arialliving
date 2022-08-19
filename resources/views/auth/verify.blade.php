@extends('layouts.auth')
@section('title', 'Confirm your Email Address | '.config('app.name') )

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
                    <h5>{{ __('Verify Your Email Address') }}</h5>
                </div>

                @if (session('resent'))
                    <div class="alert alert-success" role="alert">
                        {{ __('A fresh verification link has been sent to your email address.') }}
                    </div>
                @endif

                {{ __('Before proceeding, please check your email for a verification link.') }}
                {{ __('If you did not receive the email') }}, <a href="{{ route('verification.resend') }}">{{ __('click here to request another') }}</a>.
            </div>
        </div>
    </div>
</div>
@stop