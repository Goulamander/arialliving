{{-- Terms  --}}
@if($item->getPDFTerms())
<div class="booking-terms">
    <ul id="terms-list" class="terms-list">
        @foreach($item->getPDFTerms() as $pdf)
            @php
            $sub_html = "
                <h4>These are the Terms &amp; Conditions. Please read carefully and click accept to continue.</h4>
                <div class='form-group'>
                    <div class='checkbox'>
                        <input type='checkbox' name='accept_term' id='accept_".str_slug($pdf->filename)."' value='{$pdf->filename}'>
                        <label for='accept_".str_slug($pdf->filename)."'><span>I accept <b>{$pdf->filename}</b></span></label>
                    </div>
                </div>";
            @endphp
            <li>
                <a class="term-link" data-iframe="true" href="{{$pdf->path}}" data-sub-html="{!! $sub_html !!}">{{$pdf->filename}}</a>
            </li>
        @endforeach
    </ul>
    <div class="form-group text-left">
        <div class="checkbox accept-all-terms mt-4">
            <input type="checkbox" name="accepted_terms" value="" required/>
            <label id="open_terms"><span>I accept the <b>Terms &amp; Conditions</b></label>
        </div>
    </div>
</div>
@endif

{{-- Signature --}}
@if($item->is_signature_required)
<div class="signature_box form-group text-left">
    <canvas id="resident_signature"></canvas>
    <label class="control-label">Signature here</label><br>
    <small class="light">Use your screen, trackpad or mouse.</small>
    <div class="buttons">
        <button type="button" class="clearSignature btn btn-sm btn-simple">Clear</button>
        <button type="button" class="undoSignature btn btn-sm btn-simple">Undo</button>
    </div>
</div>
@endif