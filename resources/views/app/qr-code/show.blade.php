@extends('layouts.dashboard')

@section('title', $qr_code->name . ' | ' . config('app.name'))


@section('content')
    @include('layouts.messagesTemplate')


    <div class="panel">
        <div class="panel-header"></div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-12 col-lg-6">
                    <div class="card">
                        <div class="header">
                            <h2>
                                <strong>{{ $qr_code->name }} {!! $qr_code->getStatus() !!}</strong>
                            </h2>
                        </div>
                        <div class="body">
                            <form method="POST" autocomplete="off" action="{{ route('app.qr-code.update', $qr_code->id) }}">
                                @csrf
                                <input name="_method" type="hidden" value="PUT">
                                @php $fields = json_decode(json_encode(\App\Models\QrCode::form_fields())); @endphp

                                @foreach ($fields as $key => $field)
                                    {!! App\Helpers\FormHelper::getFields($key, $field, $qr_code) !!}
                                @endforeach
                                @if ($can_edit)
                                    <div class="clearfix"></div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="submit" name="store"
                                                class="btn btn-sm btn-primary float-right">Save changes</button>
                                        </div>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-6">
                    <div class="card p-4" id="print-content">
                        <h1 style="margin-bottom: 30px">{{ $qr_code->name }}</h1>
                        <div style="margin-bottom: 30px; text-align: justify;">{{ $qr_code->description }}</div>
                        <div style="text-align: center">
                            <img class="mw-100" src="data:image/png;base64, {!!  base64_encode(
                                SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')
                                    ->size(300)
                                    ->generate($qr_code->content),
                            ) !!} ">
                        </div>
                    </div>
                    <div class="text-right"><button class="btn btn-primary mt-2" id="print_qr_code">Print</button></div>
                </div>
            </div>
        </div>
    </div>

@endsection


@section('scripts')

    <script>
        $(document).on('click', '#print_qr_code', function() {
            var printContents = document.getElementById('print-content').innerHTML;
            w = window.open();
            w.document.write(printContents);
            w.print();
            w.close();
        })

    </script>

@endsection
