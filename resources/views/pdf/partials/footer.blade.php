<footer>
    {{-- Line items summary --}}
    <table class="line-items-footer">
        <thead>
            <tr>
            @if( $pdf != 'receipt' )

                {{-- subtotal --}}
                <th width="25%">Subtotal</th>

                {{-- discount --}}
                @if($data->discount != '0.00')
                <th width="25%">Discount</th>
                @endif

                {{-- inc. tax --}}
                <th width="25%">inc. {{config('settings')['business.locale.tax_suffix']}}</th>
                
                @switch($type)

                    @case('deposit_invoice')
                        <th width="25%">Total <small>({{config('settings')['business.locale.currency']}})</small></th>
                        <th width="25%">Deposit payable <small>({{$data->deposit_percent}}%)</small></th>
                    @break

                    @case('deposit_invoice_paid')
                        <th width="25%">Deposit paid</th>
                        <th width="25%">Total due</th>
                    @break

                    @default
                        {{-- total --}}
                        @php 
                            $letters_num = strlen(priceFormat(floatval($data->subtotal)));
                            $width = $letters_num > 11 ? 35 : 25;
                        @endphp
                        <th width="{{$width}}%">Total <small>({{config('settings')['business.locale.currency']}})</small></th>
                    @break

                @endswitch

            @else
                <th width="25%">Transaction Fee</th>
                <th width="25%">Amount received <small>({{config('settings')['business.locale.currency']}})</small></th> 
            @endif

            </tr>
        </thead>
        
        <tbody>
            <tr>
                <td>{{priceFormat($data->subtotal)}}</td>
                @if($data->discount != '0.00')
                <td class="font-red">{{priceFormat($data->discount)}}</td>
                @endif
                <td>{{ priceFormat($data->GST) }}</td>

                @if( $pdf != 'receipt' )

                    @switch($type)

                        {{-- deposit invoice --}}
                        @case('deposit_invoice')
                            <td>{{ priceFormat(floatval($data->subtotal) - floatval($data->discount)) }}</td>
                            <td class="total">{{ priceFormat($data->deposit_total) }}</td>
                        @break


                        {{-- invoice with paid deposit --}}
                        @case('deposit_invoice_paid')
                            @php
                                $TotalAmount = 0;
                                foreach($data->depositInvoice->transactions as $tr) 
                                {
                                    if($tr->TransactionStatus != 1) {
                                        continue;
                                    }
                                    $TotalAmount += $tr->TotalAmount;
                                }
                            @endphp
                            <td class="total">{{ priceFormat($invoice->depositInvoice->deposit_total) }}</td>
                            <td class="total">{{ priceFormat($data->total - $TotalAmount) }}</td>
                        @break


                        @default
                            <td class="total">{{ priceFormat(floatval($data->subtotal) - floatval($data->discount)) }}</td>
                        @break
                    @endswitch

                {{-- payment receipt --}}
                @else   
                    @php

                    $TotalAmount = 0;

                    foreach($data->transactions as $tr)  {
                        if($tr->TransactionStatus != 1) {
                            continue;
                        }
                        $TotalAmount += $tr->TotalAmount;
                    }
                    @endphp
                    <td class="total">{{ priceFormat($TotalAmount + $TotalFee) }}</td>
                @endif
            </tr>
        </tbody>
    </table>


    <div class="bottom">

        @if( $pdf == 'receipt' )

            <h4>Thank you for your payment.</h4>
            This is not a tax invoice. Receipt only.

        @else

        @endif

        @if($type == 'quote')
        <h3>Terms &amp; Conditions</h3>
        @else
        <h3>Payment methods and instructions</h3>
        @endif

        @switch($type)

            @case('quote')
                @php $valid_until = date('Y-m-d', strtotime($data->sent_at . ' +'.$valid_days.' days')); @endphp
                <p class="ft-15">This quote is valid until <strong>{{dateFormat($valid_until)}}</strong> ({{$valid_days}} days from date of writing).</p> 
                @break

            @case('invoice')
                <p class="ft-15">Your outstanding amount is <strong>{{ priceFormat($data->total) }}</strong>@if($data->sent_at), due on <span class="under-line">{{ dateFormat($data->due_date) }}</span>@endif</p>
                @break

            @case('deposit_invoice')
                <p class="ft-15"><b>This is a deposit invoice.</b> Payment has been split, with the remaining amount due upon project completion or an earlier specified date. Your current payable amount is <strong>{{priceFormat($data->deposit_total)}}</strong>@if($data->sent_at), due on <span class="under-line">{{ dateFormat($data->due_date) }}</span>@endif</p>
                @break

            @case('deposit_invoice_paid')
                <strong>Amount Due:</strong> {{ priceFormat($data->total) }} {{$by}} {{ dateFormat($invoice->depositInvoice->due_date) }}
                @break
        
        @endswitch


        @if($type == 'quote')
        <div class="payment-methods">
            <strong class="mb-5" style="font-weight:900">What's next?</strong>

            @if($data->deposit_total == 0)
            <ul>
                <li>Please confirm acceptance of quote by clicking the "Accept Quote" button in your quote email</li>
                <li>Once this quote is accepted your job request becomes active in our system</li>
            </ul>
            @else 
            <ul>
                <li>1. Please confirm acceptance of quote by clicking the <strong>"Accept Quote"</strong> button in your quote email</li>
                <li>2. Once the quote is accepted our system will send you the deposit invoice of <strong>{{priceFormat($data->deposit_total)}}</strong> ({{$data->deposit_percent}}%)</li>
                <li>3. After completing your deposit payment your job request becomes active in our system</li>
            </ul>
            @endif
     

        </div>
        @else
        <div class="payment-methods">
            @if(1 == 2)
            <div class="online">
                <p><a href="https://google.com" target="_blank" class="btn">Pay online</a></p>
            </div>
            @endif
            <div class="direct @if(1 == 2) has-online @endif">
                <strong style="font-weight:900;">Direct deposit</strong>
                <ul>
                    <li class="font-dark"><strong>Bank:</strong> {{config('settings')['business.bank.bank_name']}}</li>
                    <li class="font-dark mb-5"><strong>BSB / Account No:</strong> {{config('settings')['business.bank.bank_account_number']}}</li>
                    
                    <li class="small">Please include <b>{{ $data->getNumber() }}</b> in your payment description.</li>
                    @if(config('settings')['business.bank.accounts_email'])
                        <li class="small">Please email remittance to <a href="mailto:{{config('settings')['business.bank.accounts_email']}}">{{config('settings')['business.bank.accounts_email']}}</a></li>
                    @endif
                </ul>
            </div>
        </div>
        @endif

        <p class="light">Questions? Get in touch <a href="mailto:{{config('settings')['email.settings.reply_address']}}">{{config('settings')['email.settings.reply_address']}}</a></p>
    </div>
</footer>