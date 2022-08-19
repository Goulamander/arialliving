@extends('layouts.dashboard')

@section('title', 'Media | '.config('app.name'))


@section('content')
@include('layouts.messagesTemplate')
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css">
<link rel="stylesheet" href="{{ asset('vendor/file-manager/css/file-manager.css') }}">

<style>
.fm .fm-modal{
    position: fixed;
    z-index: 99999;
    overflow: auto;
    display: flex;
    place-content: center;
    place-items: center;
}
.fm .fm-modal > div{
    margin-top: 0;
    min-width: 400px;
}
.fm .fm-modal .fm-modal-preview .modal-body img{
    max-height: 60vh !important;
    min-height: 30vh !important;
}
.fm .fm-modal .modal-content .modal-header{
    display: flex;
    place-content: space-between;
    place-items: center;
    padding: 10px;
    border-bottom: 1px solid #e9ecef;
}
.fm .fm-modal .modal-content .modal-header .close{
    float: initial;
    position: initial;
}
.fm .fm-modal .modal-content .modal-header h5{
    font-size: 120%;
}
.fm .fm-modal .fm-modal-preview .d-flex.justify-content-between{
    padding: 10px;
}
.fm .fm-modal .fm-modal-preview .d-flex.justify-content-between button{
    margin: 0;
    padding: 7px 10px;
}
.fm .fm-modal .fm-modal-properties .modal-body .row .fa-copy{
    position: absolute;
}
.fm .fm-navbar button[title="Hidden files"],
.fm .fm-navbar button[title=" Hidden files"],
.fm .fm-navbar button[title="Full screen"],
.fm .fm-navbar button[title="About"]{
    display: none !important;
}
.fm .fm-table .fas.fa-level-up-alt:after{
    content: '...';
    font-weight: normal;
    margin-left: 10px;
}
.fm .fm-body {
    min-height: 50vh;
}
</style>


<div class="row clearfix">
    <div class="col-lg-12">
        <div class="card">
            <div class="header pb-0">
                <h2><strong>Media</strong> management</h2>
            </div>

            <div class="body">
                <div style="min-height: 70vh;">
                    <div id="fm"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="{{ asset('vendor/file-manager/js/file-manager.js') }}"></script>
@endsection


@section('scripts')

@endsection