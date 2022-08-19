<div id="mod-retail-deal" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content" style="overflow:visible">
            <div class="modal-header">
                <h3>{{$deal->name ?? 'Add New Deal'}}</h3>
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" action="{{ route('app.deal.store', ['store_id' => $store->id ?? 0, 'deal_id' => $deal->id ?? 0]) }}" method="POST">
                    {{ csrf_field() }}
                
                    <div class="inline-uploader" data-name="thumb">
                        <input type="file" name="thumb">
                    </div>
         
                    @php $fields = json_decode(json_encode(\App\Models\RetailDeal::form_fields())); @endphp
                    @foreach($fields as $key => $field)
                        {!! App\Helpers\FormHelper::getFields($key, $field, $deal ?? '') !!}
                    @endforeach
                    <div class="modal-form-footer text-right mt-3">
                        <button type="button" data-dismiss="modal" class="btn btn-simple modal-close">Cancel</button>
                        <button type="submit" class="btn btn-primary modal-close">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>