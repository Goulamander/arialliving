
<div id="mod-category" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog" style="max-width:500px">
        <div class="modal-content" style="overflow:visible;">
            <div class="modal-header">
                <h3>Add new category</h3>
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{route('app.settings.category.store')}}" autocomplete="off">
                    {{ csrf_field() }}

                    @php $fields = json_decode(json_encode(\App\Models\Category::$form_fields)); @endphp
                    
                    @foreach($fields as $key => $field)
                        {!! App\Helpers\FormHelper::getFields($key, $field) !!}
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
