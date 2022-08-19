<div id="mod-event" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content" style="overflow:visible">
            <div class="modal-header">
                <h3>{{$item->title}}</h3>
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{route('app.item.store', ['type' => 'event', 'item_id' => $item->id])}}" data-reload="true" autocomplete="off">
                    {{ csrf_field() }}

                    @php 
                        $fields = json_decode(json_encode(\App\Models\BookableItem::form_event_fields())); 
                        $item['all_day'] = '0';
                    @endphp

                    @foreach($fields as $key => $field)
                        {!! App\Helpers\FormHelper::getFields($key, $field, $item) !!}
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