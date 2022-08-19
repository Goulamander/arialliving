@extends('layouts.dashboard')

@section('title', 'Retail Deals | '.config('app.name'))

@section('content')
@include('layouts.messagesTemplate')
<div class="panel frontEnd">
    <div class="panel-body">
        <div class="row">
            <div class="col-8">
                <h1>Retail Deals</h1>
                {{-- <h4>Browse the best deals form retails in your doorstep</h4> --}}
                {{-- <h4>Browse the best deals from retailers at your doorstep. Coming soon!</h4> --}}
            </div>
            <div class="col">
                <a href="{{route('resident.deals.history')}}" class="btn btn-brand btn-lg btn-round float-right">Redeem History</a>
            </div>
        </div>
        
        @if($building->retailDeals)
        <div id="retailDeals" class="row deals">

            @foreach($building->retailDeals as $deal)

            @if($deal->is_hidden)
                @continue
            @endif

            <div data-id="{{$deal->id}}" class="col-lg-4 col-md-6 col-deal">
                <div class="deal">  
                    <button class="_close no-btn">
                        <i class="material-icons">close</i>
                    </button>
                    <div class="deal_thumb" @if($deal->getThumb('820x500')) style="background-image: url('{{$deal->getThumb('820x500') ?? ''}}')" @endif>
                        @if(!$deal->allowed_redeem_num)
                            <span class="label l-green">Unlimited Redeem</span>
                        @elseif($deal->redeem_num > 0)
                            @php $times = $deal->allowed_redeem_num - $deal->redeem_num; @endphp
                            <span class="label l-green _counter">Redeem {{$times}} more {{str_plural('time', $times)}}</span>
                        @endif
                        <div class="store">
                            <div class="store-logo" @if($deal->store->getThumb('820x500')) style="background-image: url('{{$deal->store->getThumb('180x180') ?? ''}}')" @endif></div>
                            {{$deal->store->name}}
                        </div>
                    </div>
                    <div class="deal-body">
                        <h3>{{$deal->title}}</h3>
                        @if($deal->subtitle)
                            <h4>{{$deal->subtitle}}</h4>
                        @endif
                        {{-- <p class="short-desc">{{first_sentence($deal->description)}}</p> --}}
                        <p class="short-desc">{{$deal->description}}</p>
                    </div>
                    <div class="deal-extended-body">
                        <div class="deal-description">
                            {{$deal->description}}
                        </div>
                        <div class="deal_redeem">
                            <h5>To redeem, show this screen instore to staff and press 'Redeem Now'</h5>
                            <p>Once redeemed, this offer may be removed from your dashboard, so press 'Redeem  Now' only in front of staff.</p>
                            <button type="button" name="redeem" value="{{$deal->id}}" class="btn btn-lg btn-primary">I'm in the store. REDEEM NOW</button>
                        </div>
                        @if($deal->terms)
                        <div class="deal-terms">
                            <h4>Terms</h4>
                            {{$deal->terms}}
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach

        </div>
        @endif
        
    </div>
</div>
@endsection


@section('scripts')
@endsection    