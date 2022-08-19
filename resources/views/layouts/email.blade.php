<?php

use Carbon\Carbon;

$date = Carbon::now();

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" type="text/css" href="{{asset('/lib/ionicons/css/ionicons.min.css')}}">
        <style>
            body {
                font-family: "Roboto", Helvetica Neue,Helvetica,Arial,sans-serif;
                color: rgb(78, 93, 120);
                font-weight: 300;
                font-size: 16px;
                line-height: 1.6;
            }
            .aria--email {
                box-sizing: border-box;
                border: 0 none !important;
                text-align: left;
                margin: auto;
                width: 100%;
                max-width: 850px;
                float: none;
                padding: 0;
                padding: 25px;
            }
            .aria--email .header {
                background-color: #191f28;
                padding: 30px 20px;
                text-align: left;
                margin-bottom: 30px;
            }
            .aria--email .footer {
                margin-top: 50px;
                border-top: 2px solid #e3e3e3;
                padding-top: 10px;
            }
            .logo img {
                max-width: 130px;
                width: 100%;
                height: auto;
            }
            .social {
                border-top: 1px solid #7ccfaf;
                text-align: left;
                padding-top: 5px;
            }
            p {
                font-size: 16px;
                line-height: 1.6;
            }
            h1,h2,h3,h4 {
                color: #3e485a;
                font-weight: 500;
            }
            h1, h2 {
                font-size: 25px;
            }
            h3, h4 {
                font-size: 20px;
            }
            .social a {
                display: inline-block;
                width: 18px;
                height: 20px;
                padding: 5px;
                text-align: center;
            }
            .social a img {
                width: 18px;
                height: auto;
            }
            svg {
                width: 16px;
                height: 20px;
                margin: auto;
                display: table;
                fill: #369079;
            }
            a:hover svg {
                fill: #245d6a;
            }
            .btn {
                background-color: #191f28;
                display: inline-block;
                padding: 10px 20px;
                margin-bottom: 0;
                font-size: 13px;
                font-weight: 300;
                line-height: 1.42857143;
                text-align: center;
                white-space: nowrap;
                vertical-align: middle;
                -ms-touch-action: manipulation;
                touch-action: manipulation;
                cursor: pointer;
                -webkit-user-select: none;
                -moz-user-select: none;
                -ms-user-select: none;
                user-select: no;
                border-radius: 40px;
                text-decoration: none;
                color: #fff;
            }
            .btn:hover,
            .btn:active,
            .btn:focus {
                color: #fff;
                background-color: #000;
            }
            .btn-g {
                color: #fff;
                background-color: #54d5c1;
                border-color: #54d5c1;
            }
            .btn-g:hover {
                color: #fff;
                background-color: #42a797;
                border-color: #42a797;
            }
            a {
                color: #245d6a;
                text-decoration: none;
            }
            a:hover {
                color: #31ad74;
            }
        </style>
    </head>
    <body>
        <div class="aria--email">
            <div class="header">
                <a href="{{route('app.index')}}" class="logo">
                    <img src="{{ asset('img/logo.png') }}" alt="{{env('APP_NAME')}}">
                </a>
            </div>
            <div class="body">
                @yield('content')
            </div>
            <div class="footer">
                <small>© {{$date->format('Y')}} Aria Living - Online Reservations.</small>
            </div>
        </div>
    </body>
</html>