<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('img/aria_fav.png') }}" type="image/x-icon">
    <title>@yield('title')</title>
    <meta name="description" content="@yield('meta_description')">
    @yield('meta')
    <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ mix('/css/app-aria.css') }}">
</head>

<body class="theme-aria">
    <div class="authentication">
        <div class="container">
            @yield('content')
        </div>
    </div>
    <script src="{{ mix('js/app-libs.js') }}" type="text/javascript"></script>
    <script src="{{ mix('js/app-libs-2.js') }}" type="text/javascript"></script>

    <script src="{{ mix('js/app-aria.js') }}" type="text/javascript"></script>
    <script src="{{ mix('js/app-auth.js')}}" type="text/javascript"></script>
</body>
</html>
