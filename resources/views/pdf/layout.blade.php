
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>


<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>@yield('head_title') | {{ config('settings')['business.default.name'] }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900" rel="stylesheet">
    <style>
        @page {
            margin: 10mm 15mm;
        }
        .page-break {
            page-break-after: always;
        }
        body {
            position: relative;
            font-family: 'Lato', sans-serif;
            color: #7d859c;
            font-weight: 400;
            height: 100%;
            position: relative;
            font-size: 15px;
        }
        /* Font */
        p {
            font-family: 'Lato', sans-serif;
            margin: 0;
            padding: 0;
        }
        h1,h2,h3,h4 {
            font-family: 'Lato', sans-serif;
        }
        a {
            color: #6151d8;
            text-decoration: none;
        }
        strong {
            color: #000;
        }
        small {
            font-size: 12px;
        }
        .ft-15 {
            font-size: 15px;
        }
        .lg {
            font-size: 16px;
        }
        .light {
            color: #7d859c;
        }
        .font-dark {
            color: #000;
        }
        .font-red {
            color: #dc3f5c;
        }
        .under-line {
            text-decoration: underline;
        }
        /* Grid system */
        .row {
            display: flex;
            margin-bottom: 0;
        }
        .col {
            display: block;
            width: 50%;
        }
        .offset {
            margin-left: 48%;
        }
        .small {
            font-size: 13px;
        }

        /* Layout */
        .mb-5 {
            margin-bottom: 5px;
        }
        .mb-40 {
            margin-bottom: 50px;
        }
       .btn {
           display: inline-block;
           background-color: #000;
           padding: 0 20px;
           color: #fff;
           border-radius: 20px;
           height: 38px;
           line-height: 22px;
           margin: 5px 0;
           overflow: hidden;
           cursor: pointer;
       }
        /* Header */
        header img {
            width: auto;
            height: 80px;
            margin-bottom: 15px;
        }
        header .placeholder {
            display: block;
            height: 60px;
        }


        /* Body Section */ 
        section {
            border-top: 4px solid #f6f5fb;
            margin-top: 10mm;
            margin-bottom: 15mm;
        }

        /* Line items */
        table.line-items,
        table.line-items-footer {
            color: #000;
            width: 100%;
            padding: 0;
            font-size: 15px;
            page-break-inside:auto;
            page-break-before:auto;
            page-break-after:auto;
            border-spacing: 0;
        }
        table.line-items thead tr th {
            padding: 10px 0;
            font-weight: 900;
        }
        table.line-items tbody td {
            border-collapse: collapse;
            padding: 10px 0;
        }
        table.line-items thead tr th,
        table.line-items tbody tr td {
            text-align: left;
        }

        /* Line items summary section */
        table.line-items-footer thead tr th {
            padding-top: 5mm;
            text-transform: uppercase;
            text-align: left;
            padding-bottom: 10px;
        } 
        table.line-items-footer .total {
            font-size: 30px;
            font-weight: 400;
        }


        /* Deposit received */

        .deposit-line {
            background-color: #EFF6F6;
            float: right;
            display: block;
            border-radius: 3px;
            color: #3d4b61;
            font-size: 12px;
            line-height: 1.5;
            width: auto;
            padding: 4px 12px;
            text-align: right;
            margin: 15px 0 25px;
        }
        .deposit-line strong {

        }
        .deposit-line + table.line-items {
            margin-top: 70px;
        }

        /** Footer */
        footer {
            display: block;
            color: #000;
            font-size: 14px;
        }
        footer table {
            border-top: 4px solid #f6f5fb;
            margin: 0;
        }

        footer .bottom {
            page-break-inside: avoid;
            page-break-before: initial;
            display: block;
            position: relative;
            bottom: 0;
            z-index: 99;
            margin-top: 0;
            padding-top: 5mm;
        }

        footer .bottom ul {
            position: relative;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        footer .bottom .payment-methods {
            background-color: #f6f5fb;
            page-break-inside: avoid;
            position: relative;
            display: block;
            width: 100%;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
            margin-bottom: 8px;
        }
        footer .bottom .payment-methods:before,
        footer .bottom .payment-methods:after {
            clear: both;
        }
        footer .bottom .payment-methods .online {
            page-break-inside: avoid;
            position: absolute;
            left: 22px;
            margin-top: 22px;
            width: 140px;
        }

        footer .bottom .payment-methods .direct {
            page-break-inside: avoid;
        }
        footer .bottom .payment-methods .direct.has-online {
            border-left: 1px dashed #7d859c; 
            padding-left: 20px;
            margin-left: 140px !important;
        }

        footer .bottom .payment-methods ul {
            margin-top: 5px;
            display: block;
        }
    </style>
</head>
<body>
    @yield('body')
</body>
</html>
