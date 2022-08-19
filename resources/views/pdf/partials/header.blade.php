
<header>
    {{-- Business --}}
    <div class="row">
        <div class="col offset mb-40">
            @if(getAppLogoUrl(true))
            <img class="logo" src="{{getAppLogoUrl(true)}}" alt="{{config('settings')['business.default.name']}}">
            @else
            <div class="placeholder"></div>
            @endif
            <div class="details">
                <p><strong class="lg">{{ config('settings')['business.default.name'] }}</strong></p>
                <p>{!! config('settings')['business.default.billing_address'] !!}</p>
                @if(config('settings')['business.default.abn'])
                <p>ABN {{config('settings')['business.default.abn']}}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- document details --}}
    <div class="row document">
        <div class="col">
            @if($pdf == 'receipt')
            <strong>Payment Receipt</strong>
            @else
            <strong>{{$title}}@if($data->status == 0) <span class="light">(Draft)</span> @endif</strong>
            @endif
        </div>

        <div class="col offset">
            @if( $pdf == 'receipt' )

            <div class="details">
                <small>Paid for Invoice No.</small>
                <strong>{{ $data->getNumber() }}</strong>

                <small>Transaction No.</small>
                <strong>{{ $data->transaction->getNumber() }}</strong>
                
                <small>Paid Date</small>
                <strong>{{ dateFormat($data->transaction->created_at) }}</strong>
            </div>

            @elseif($type == 'quote')

            <div class="details">
                <p><strong>{{$data->getNumber()}}</strong></p>
                <p>@if($data->sent_at) {{ dateFormat($data->sent_at) }} @else - @endif</p>
            </div>

            @else
            {{-- Invoice --}}
            <div class="details">
                <p><strong>{{$data->getNumber()}}</strong></p>
                <p>@if($data->invoiced_date) {{ dateFormat($data->invoiced_date) }} @else - @endif</p>
            </div>

            @endif
        </div>
    </div>

    {{-- client --}}
    <div class="row client">
        <div class="col">
            <strong>Client</strong>
        </div>
        <div class="col offset">
            @if($data->user->profile && $data->user->profile->billing_name)
            <p><strong>{{$data->user->profile->billing_name}}</strong></p>
            @else
            <p><strong>{{$data->user->name}}</strong></p>
            @endif

            @if( $data->user->getBillingAddress() )
            <p>{{ $data->user->getBillingAddress(true) }}</p>
            @elseif( $data->user->getPrimaryAddress() ) 
            <p>{{ $data->user->getPrimaryAddress(true) }}</p>
            @endif

            @if( $data->user->profile )
            <p>{{ $data->user->profile->phone ?? $data->user->profile->mobile }}</p>
            @endif

            {{ $data->user->email ?? '' }}
        </div>
    </div>
</header>