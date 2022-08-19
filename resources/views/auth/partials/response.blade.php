@if (!$errors->isEmpty())
<div class="form-group box-error">
    <ul>
    @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach
    </ul>
</div>
@endif

@if(session()->has('message.success'))
<div class="form-group box-success"> 
    {!! session('message.success') !!}
</div>
@endif

@if (session('success'))
<div class="form-group box-success">
    {!! session('success') !!}
</div>
@endif

@if (session('activationSuccess'))
<div class="form-group box-success">
    {{ session('activationSuccess') }}
</div>
@endif

@if (session('accountDeleteSuccess'))
<div class="form-group box-success">
    {{ session('accountDeleteSuccess') }}
</div>
@endif

@if (session('status'))
<div class="form-group box-success">
    {!! session('status') !!}
</div>
@endif
