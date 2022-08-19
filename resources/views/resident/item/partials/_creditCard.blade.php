<div id="credit-card" class="credit-card">
    @php $card_required_class = 'required'; @endphp
    @if(Auth::user()->card_details)
        <div class="credit-card--row">
            @php 

            $card_required_class = '';
            $card = json_decode(Auth::user()->card_details);
            
            $expiry_in = Auth::user()->getCardExpiry();
            $expiry_in_str = "in {$expiry_in} ".Illuminate\Support\Str::plural('days', $expiry_in);
            $exp_soon_class = '';
            
            if($expiry_in <= 60) {
                $exp_soon_class = ' badge-danger';
            }
            if($expiry_in <= 0) {
                $expiry_in_str = "Expired";
            }

            @endphp
            <img src="/img/{{Illuminate\Support\Str::slug($card->type)}}-logo.jpg" alt="{{$card->type}}"/>
            <h3>{{ucwords($card->type)}} **** **** **** <strong>{{$card->end}}</strong></h3>
            <span class="card-exp">Expiry {{$card->exp_month}}/{{$card->exp_year}} <span class="badge{{$exp_soon_class}}">{{$expiry_in_str}}</span></span>
            <button type="button" class="btn btn-b btn-sm" data-toggle="collapse" data-target="#addCard">
                Change
            </button>
        </div>
    @elseif( isset($is_submit_btn) && $is_submit_btn )
        <h4 class="mb-2 mt-0" style="font-size: 16px">No credit card attached to your account yet.</h4>
        <p class="mb-2"><small>If you're looking to optimize the checkout experience for future bookings simply add your Visa or MasterCard here.</small></p>
        <button type="button" class="btn btn-b btn-sm mb-4" data-toggle="collapse" data-target="#addCard">
            Add Card
        </button>
    @endif
    <div id="addCard" class="collapse @if(!Auth::user()->card_details && ! isset($is_submit_btn)) show @endif">
        <div class="form-group">
            <label class="control-label">Name on Card</label>
            <input class="form-control" type="text" name="card_name" placeholder="Name on Card" {{$card_required_class}}>
        </div>
        <div class="form-group">
            <label class="control-label">Card Number</label>
            <input class="form-control credit-input" type="text" id="card_number" placeholder="xxxx xxxx xxxx xxxx" {{$card_required_class}}>
        </div>
        <div class="row">
            <div class="col-sm-6 col-6">
                <div class="form-group">
                    <label class="control-label">Expiry Date <small>(mm/yy)</small></label>
                    <?php

                    $exp_month = \Carbon\Carbon::now()->format('m');
                    $exp_year  = \Carbon\Carbon::now()->format('y');

                    ?>
                    <div class="row">
                        <div class="col-6">
                            <select class="form-control" name="card_expiry_month" {{$card_required_class}}>
                            @for ($i = 1; $i <= 12; $i++)
                                <?php

                                $s = sprintf('%02d', $i);
                                $selected = ($exp_month == $i) ? ' selected' : null;

                                ?>
                                <option value="{{$s}}"{{$selected}}>{{$s}}</option>
                            @endfor
                            </select>
                        </div>
                        <div class="col-6">
                            <select class="form-control" name="card_expiry_year" {{$card_required_class}}>
                                @for ($i = \Carbon\Carbon::now()->format('y'); $i < \Carbon\Carbon::now()->format('y')+11; $i++)
                                    <?php $selected = ($exp_year == $i) ? ' selected' : null; ?>
                                    <option value='{{$i}}'{{$selected}}>{{$i}}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-3 col-6">
                <div class="form-group">
                    <label class="control-label">CVN <span class="card-feedback"></span></label>
                    <input class="form-control" type="text" id="card_cvn" maxlength="4" placeholder="CVN" {{$card_required_class}}>
                </div>
            </div>
            <div class="col-sm-3 col-12">
                <img class="card-logos" src="/img/Mastercard-Visa.png" width="150" alt="We accept Visa and MasterCard">
            </div>
        </div>
        @if( isset($is_submit_btn) && $is_submit_btn )
        <div class="row">
            <div class="col-md-12">
                <button type="submit" name="store" class="btn btn-primary float-right">Save card</button>
            </div>
        </div>
        @endif
    </div>
</div>