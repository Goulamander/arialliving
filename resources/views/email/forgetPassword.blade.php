@extends('layouts.email')

@section('content')
    <p>A password reset request was initiated for the {{$email}} account with Aria Living. Click below to reset your password.</p>
    <p><a href="{{url('password/reset', $token)}}" class="btn btn-success">Reset Password</a></p>
@endsection