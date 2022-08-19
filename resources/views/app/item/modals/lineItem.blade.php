<div id="mod-line-item" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content" style="overflow:visible">
            <div class="modal-header">
                <h3>Add New Item</h3>
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{route('app.item.lineItems.store', ['item_id' => $item->id])}}" autocomplete="off">
                    {{ csrf_field() }}

                    <div class="inline-uploader" data-name="thumb">
                        <input type="file" name="thumb">
                    </div>

                    @php $fields = json_decode(json_encode(\App\Models\LineItem::$form_fields)); @endphp
                    @foreach($fields as $key => $field)
                        {!! App\Helpers\FormHelper::getFields($key, $field, $item->line_items) !!}
                    @endforeach

                    <div class="modal-form-footer text-right mt-3">
                        <button type="button" data-dismiss="modal" class="btn btn-simple modal-close">Cancel</button>
                        <button type="submit" class="btn btn-primary float-right modal-close">Save item</button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>