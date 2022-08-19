<div class="transaction">
    <div class="row">
        <div class="col">
            <h4 class="m-0 @if($transaction->isRefund()) refund @endif">{{priceFormat($transaction->totalAmount)}}</h4>
            {{$transaction->getNumber()}}
        </div>
        <div class="col">
            <small class="text-muted">eWay Response</small>
            <p>{!! $transaction->getStatus() !!}</p>
            <small>{{$transaction->responseMessage}}</small>
        </div>
        <div class="col">
            <small class="text-muted">Transaction Type</small>
            <p>{{$transaction->getType()}}</p>
        </div>
        <div class="col">
            <small class="text-muted">Transaction Date</small>
            <div class="_create_at">
                <p>
                {{dateFormat($transaction->created_at)}}<br>
                <small>{{timeFormat($transaction->created_at)}}</small></p>
            </div>
        </div>
        <div class="col">

        {{-- Actions --}}

        @if($transaction->transactionStatus == true)

            @switch($transaction->type)

                {{-- Booking fee --}}
                @case(\App\Models\Transaction::$TYPE_BOOKING_FEE)
                    @if($transaction->canRefund())
                        {{-- Refund button --}}
                        <button type="button" data-toggle="modal" data-target="#mod-refund" data-route="{{route('app.transaction.refund', $transaction->id)}}" data-fill="amount:{{$transaction->totalAmount}}" class="btn btn-sm btn-brand refund_btn md-trigger">Refund</button>
                    @else 
                        {{-- Refund details --}}
                        <small class="text-muted">Refunded</small>
                        <p>{{\App\Models\Transaction::staticGetNumber($transaction->refund_id)}}</p>
                        @if($transaction->refundTransaction->createdBy)
                            <small>by {{$transaction->refundTransaction->createdBy->fullName()}}</small>
                        @endif
                    @endif
                    @break

                {{-- Bond Holding --}}
                @case(\App\Models\Transaction::$TYPE_BOND)
                    @if($transaction->canRelease())
                        {{-- Release button --}}
                        <button type="button" data-toggle="modal" data-target="#mod-bond-release" data-route="{{route('app.transaction.releaseBond', $transaction->id)}}" data-fill="amount:{{$transaction->totalAmount}}" class="btn btn-sm btn-primary refund_btn md-trigger">Release Bond</button>
                    @else
                        {{-- Release details --}}
                        <small class="text-muted">Released</small> 
                        <p>{{\App\Models\Transaction::staticGetNumber($transaction->release_id)}}</p>
                        @if($transaction->releaseTransaction->createdBy)
                            <small>by {{$transaction->releaseTransaction->createdBy->fullName()}}</small>
                        @endif
                    @endif
                    @break

                @default             
                    <small class="text-muted">Notes</small>       
                    <p><small>{{$transaction->notes ?? '-'}}</small></p>
                    @break
            @endswitch



        @else
            @if( $transaction->canRetry() )
            <form action="{{route('app.transaction.retry', $transaction->id)}}" method="POST">
                <button type="submit" class="btn btn-sm btn-brand refund_btn md-trigger">Retry</button>
            </form>
            @endif
        @endif
        </div>
    </div>

</div>