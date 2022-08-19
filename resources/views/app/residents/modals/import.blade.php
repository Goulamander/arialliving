<div id="mod-import-resident" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content" style="overflow:visible">
            <div class="modal-header">
                <h3>Import Resident</h3>
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <div class="text-right">
                    <a href="{{ asset('templates/Import-Residents-Template.csv') }}" class="text-primary" download="Import Residents Template.csv">Download Import template</a>
                </div>
                <form class="jsSubmit" autocomplete="off" action="{{ route('app.resident.import') }}" data-reset="true" method="POST" enctype="multipart/form-data">
                    {{ csrf_field() }}

                    <div class="form-group _csv_file ">
                        <label class="control-label">CSV File</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required="" />
                    </div>
                    <div class="form-group _invite_resident checkbox mb-4 mt-3 float-right">
                        <div class="checkbox">
                            <input type="checkbox" id="invite_residents" name="invite_residents" class="" value="1"
                                data-parsley-multiple="invite_residents" checked>
                            <label for="invite_residents">Send invitation email to residents on creation?</label>
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
