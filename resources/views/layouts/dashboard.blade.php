<?php

/**
 *  Frame for Dashboard
 *
 * @return Response
 */

use Carbon\Carbon;

    $date = Carbon::now();
    $is_resident = Auth::user()->isResident();
?>

@section('title', 'Dashboard '.config('app.name'))

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('img/aria_fav.png') }}">
    <title>@yield('title')</title>
    <link rel="stylesheet" type="text/css" href="{{ mix('css/app-bootstrap.css') }}">
    <!-- Theme -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/main.css') }}">
    <!-- Libs -->
    <link rel="stylesheet" type="text/css" href="{{ mix('css/app-libs.css') }}">
    <!-- Aria -->
    <link rel="stylesheet" type="text/css" href="{{ mix('css/app-aria.css') }}">
    
    @yield('page-styles')
    @yield('stylesheets')

    <script>window.Laravel = {!! json_encode(['csrfToken' => csrf_token()]) !!}</script>
</head>

<body class="dash theme-aria">

    <div class="overlay"></div>

    {{-- include the hero on the front page --}}
    @php 
        $is_front_layout = Request::is('/') || str_contains(url()->current(), '/preview-building')  ? true : false; 
        $is_preview = str_contains(url()->current(), '/preview-building')  ? true : false; 
    @endphp

    @if( $is_front_layout ) 
    @php 

    $style = '';
    if($resident_building->image) {
        $style = 'style="background-image: url('.\Storage::url($resident_building->image).')"';
    }
    @endphp
    <div class="aria-hero"{!! $style !!}>
        <div class="aria-hero--container">
            <div class="logo_wrap">
                <img src="/img/logo.png" width="220" alt="Aria Living" class="hero-logo">
                <h3>{{$resident_building->name}}</h3>
            </div>
        </div>
    </div>
    @endif

    @include('layouts.Navbar')

    @if( ! $is_resident ) 
        @include('layouts.Sidebar')
    @endif

    @yield('top-section')

    <section class="content _main">

        {{-- Admin general Layout --}}

        @if( ! Request::is('admin'))
        <div class="container">
            @yield('content')
            <div class="row clearfix">
                <div class="col mt-4 mb-3">
                    <p class="copyright">Copyright {{ date('Y') }} © All Rights Reserved.</p>
                </div>
            </div>
        </div>
        @else
            @yield('content')
        @endif
    </section>


    {{-- Admin impersonating user --}}
    @if( app('impersonate')->isImpersonating() )
    <div class="admin_control_bar">
        <div class="back_to_admin">
            <a href="{{ route('impersonate.leave') }}" class="btn btn-sm ml-3 btn-round btn-purple btn-arrow">Log back as admin <i class="material-icons">arrow_forward</i></a>
        </div>
    </div>
    @else
        {{-- Admin Building Preview --}}
        @if(!Auth::user()->isResident())
            @if(strpos(url()->current(), 'admin') === false)
                @include('resident.adminController')
            @else
                @include('layouts.admin-bottom-action')
            @endif
        @endif
    @endif

    {{-- Modals --}}
    @yield('modals')
    {{-- Global Modals --}}
    @include('app._partials.modals.confirmDelete')

    <!-- Libs -->
    <script src="{{ mix('js/app-libs.js') }}" type="text/javascript"></script>
    <script src="{{ mix('js/app-libs-2.js') }}" type="text/javascript"></script>

    <!-- Theme -->
    <script src="{{ asset('assets/bundles/vendorscripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/admin.js') }}"></script> 

    <script type="text/javascript">
    window.Laravel = {!! json_encode([
        'config' => [
            'encrypt_key' => config('eway.encrypt_key') 
        ]
    ]) !!}
    window.isAdmin = !!"{{Auth::user()->hasRole(['super-admin', 'admin'])}}"; // check global isAdmin

    </script>
    @yield('scripts_before_libs')
    
    <!-- Aria -->
    <script src="{{ mix('js/app-aria-0.js') }}" type="text/javascript"></script>
    <script src="{{ mix('js/app-aria.js') }}" type="text/javascript"></script>

    <script type="text/javascript">App.init()</script>

    <!-- Events Listener -->
    @if(\Auth::check() && env("SOCKET_HOST") !== 'FALSE')
        <!-- <script src="/js/events-listener.js"></script> -->
    @endif

    @yield('scripts')
</body>
</html>
