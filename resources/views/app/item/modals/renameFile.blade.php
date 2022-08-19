<div id="mod-rename-file" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content" style="overflow:visible">
            <div class="modal-header">
                <h3>Rename file</h3>
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{route('file.rename')}}" autocomplete="off">
                    
                    {{ csrf_field() }}

                    <div class="form-group">
                        <label class="control-label">File Name</label>
                        <input type="text" class="form-control" name="new_name" value="" required/> 
                        <input type="hidden" name="file_path" value="" required/> 
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