<section>
    {{-- Line items --}}
    <table class="line-items">
        <thead>
            <tr>
                <th width="50%">Description</th>
                <th width="20%">Qty</th>
                <th width="20%">Price</th>
                <th width="15%">Total</th>
            </tr>
        </thead>
        <tbody>
        @if($data->items)
            @foreach($data->items as $item)
            <tr>
                <td>
                @php
                if($data->recurring) {
                    $__description = str_replace("[invoicing_period]", '<b>'.$data->recurring->getShortcodeVal().'</b>', $item->description);
                }
                else {
                    $__description = $item->description;
                }
                @endphp
                {!! nl2br($__description) !!}
                </td>
                <td>{{$item->qty}}</td>
                <td>{{priceFormat($item->amount)}}</td>
                <td>{{priceFormat($item->qty * $item->amount)}}</td>
            </tr>
            @endforeach
        @endif
        </tbody>
    </table>
</section>