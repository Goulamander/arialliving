<!DOCTYPE html>
<html>
    <head>
        <title>Aria Living | page not found</title>

        <link rel="stylesheet" href="{{ asset('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
        <!-- <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}"> -->
        <link rel="stylesheet" type="text/css" href="{{ mix('/css/app-aria.css') }}">

        <style>
            html,
            body {
                height: 100%;
            }
            body {
                font-family: "Bliss", Arial, sans-serif;
                margin: 0;
                padding: 0;
                width: 100%;
                color: #37454c;
                display: table;
                background: #191f28;
                font-size: 18px;
            }
            .container {
                border-radius: 5px;
                text-align: center;
                display: table;
                vertical-align: middle;
                background-color: #fff;
                width: 640px;
                padding: 7em 3em;
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                margin: auto;
            }

            @media(max-width: 610px){
                .container{
                    width: 100%;
                    padding: 0;
                    display: table-cell;
                    padding-top: 20%;
                }
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 152px;
                font-weight: 100;
            }

            .logo {
                width: 100%;
                max-width: 180px;
                display: block;
                margin: auto;
                margin-bottom: 2em;
            }

            .logo img {
                width: 100%;
            }
            .logo img:hover {
                cursor: pointer;
                filter: drop-shadow(4px 4px 4px #999);
            }
            a {
                /* color: #fff; */
                color: #37454c;
                font-weight: 100 !important;
            }
            a:hover{
                /* color: #fff; */
                text-decoration: none;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <a href="/" class="logo">
                <img src="{{asset('img/aria_logo_b.png')}}" alt="Aria Living" style="max-width: 80px;height: auto;">
            </a>
            <div class="content">
                <div class="title">404.</div>
                <p>
                    @if ($exception->getMessage() != '')
                        {{$exception->getMessage()}}
                    @else
                        Page not found. <br />Please check the web address and try again.
                    @endif
                </p>
                @if(Request::fullUrl() != URL::previous())
                    <a href="{{ URL::previous() }}" class="btn btn-default btn-outline">Go Back</a>
                @else
                    <a href="/" class="btn btn-default btn-outline">Back to homepage</a>
                @endif
            </div>
        </div>
    </body>
</html>
