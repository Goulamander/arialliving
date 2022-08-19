@if (!$errors->isEmpty())
    <div role="alert" class="alert alert-danger alert-icon alert-dismissible" data-auto-dismiss="4000">
        <button type="button" data-dismiss="alert" aria-label="Close" class="close">
            <i class="material-icons">close</i>
        </button>
        <div class="message">
            @if( count($errors->all()) == 1)
                {{ $errors->all()[0] }}
            @else
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            @endif
        </div>
    </div>
@endif

@if (session('success'))
    <div role="alert" class="alert alert-success alert-icon alert-dismissible" data-auto-dismiss="2000">
        <button type="button" data-dismiss="alert" aria-label="Close" class="close">
            <i class="material-icons">close</i>
        </button>
        <div class="message">
            {!! session('success') !!}
        </div>
    </div>
    @php session()->forget('success') @endphp

@elseif (session('note'))
    <div role="alert" class="alert alert-info alert-icon alert-dismissible" data-auto-dismiss="2000">
        <button type="button" data-dismiss="alert" aria-label="Close" class="close">
            <i class="material-icons">close</i>
        </button>
        <div class="message">
            {!! session('note') !!}
        </div>
    </div>
    @php session()->forget('note') @endphp

@elseif (session('error'))
    <div role="alert" class="alert alert-danger alert-icon alert-dismissible" data-auto-dismiss="2000">
        <button type="button" data-dismiss="alert" aria-label="Close" class="close">
            <i class="material-icons">close</i>
        </button>
        <div class="message">
            {!! session('error') !!}
        </div>
    </div>
    @php session()->forget('error') @endphp
@endif
