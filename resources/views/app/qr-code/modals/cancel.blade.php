<div id="mod-cancel" data-backdrop="static" data-keyboard="false" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog modal-dialog-centered" style="max-width:500px">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body __delete">
                <div class="text-center">
                    <h4>Are you sure you want to delete?</h4>
                    <div class="form-footer mt40 mb10">
                        <form autocomplete="off" class="mod-form jsSubmit" action="" method="POST">
                            {{ csrf_field() }}
                            {{ method_field("DELETE") }}
                            <button type="button" data-dismiss="modal" class="btn btn-simple">Close</button>
                            <button type="submit" class="btn btn-danger">Yes. Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>