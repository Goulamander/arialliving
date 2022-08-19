<div id="mod-resident-level" tabindex="-1" role="dialog" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" style="overflow:visible">
            <div class="modal-header">
                <h3>Add New Marketing Communications by Resident Level</h3>
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <form class="jsSubmit" autocomplete="off" action="{{ route('app.marketing-communications.create') }}" data-reset="true" method="POST">
                    {{ csrf_field() }}
                    @php $fields = json_decode(json_encode(\App\Models\MarketingCommunications::form_fields())); @endphp

                    @foreach($fields as $key => $field)
                        {!! App\Helpers\FormHelper::getFields($key, $field) !!}
                    @endforeach
                    <div class="form-group">
                        <label class="control-label">Body</label>
                        <div class="html_editor_wrap">
                            <div data-name="body" class="_full_html_editor"></div>
                        </div> 
                    </div> 
                    <div class="form-group _invite_resident checkbox mb-4 mt-3">
                        <div class="checkbox">
                            <input type="checkbox" id="status" name="status" class="" value="1"
                                data-parsley-multiple="status" checked>
                            <label for="status">Send Email(SMS) to resident(s) on creation?</label>
                        </div>
                    </div>

                    <div class="modal-form-footer text-right mt-3">
                        <button type="button" data-dismiss="modal" class="btn btn-simple modal-close">Cancel</button>
                        <button type="submit" class="btn btn-primary modal-close">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
