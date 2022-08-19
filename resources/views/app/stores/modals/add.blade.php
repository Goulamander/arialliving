<div id="mod-retail-store" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content" style="overflow:visible">
            <div class="modal-header">
                <h3>{{$store->name ?? 'Add New Retail Store'}}</h3>
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <form autocomplete="off" action="{{ route('app.store.store', $store->id ?? 0) }}" method="POST" @if(isset($store)) data-reload="true" @endif>
                    {{ csrf_field() }}
                    @php $fields = json_decode(json_encode(\App\Models\RetailStore::form_fields())); @endphp
                    @foreach($fields as $key => $field)
                        {!! App\Helpers\FormHelper::getFields($key, $field, $store ?? '') !!}
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