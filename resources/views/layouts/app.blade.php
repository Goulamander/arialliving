<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <script src="{{ asset('js/app.js') }}" defer></script>

    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Roboto+Condensed:400,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/lib/bootstrap-frontend/css/bootstrap.min.css">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body class="auth_page">
    <div>

        <header>
            <div class="app_logo">
                <img src="/img/logo.png" alt="logo" />
            </div>
        </header>

        <main class="py-4">
            @include('layouts.messagesTemplate')
            @yield('content')
        </main>
        
    </div>
    <script src="/lib/bootstrap-frontend/js/bootstrap.min.js" type="text/javascript"></script>
</body>
</html>
