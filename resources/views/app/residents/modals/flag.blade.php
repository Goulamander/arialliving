<div id="mod-flag-resident" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog" style="max-width:500px">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body __delete">
                <div class="text-center">
                    <i class="modal-main-icon icon-flag mb-2"></i>
                    @if($resident->is_flagged == false)
                        <h4>Flag Resident</h4>
                    @else 
                        <h4>Removing Flag from this resident?</h4>
                    @endif 
                    <div class="form-footer mt40 mb10">
                        <form action="{{route('app.user.flag', $resident->id)}}" autocomplete="off" class="mod-form" data-reload="true" method="POST">
                            {{ csrf_field() }}
                            {{ method_field('POST') }}
                            @if($resident->is_flagged == false)
                            <div class="form-group">
                                <label class="control-label">Reason for flagging?</label>
                                <textarea class="form-control" name="reason" maxlength="150" rows="3"></textarea>
                                <small>Max 150 characters</small>
                            </div>
                            @endif
                            <button type="button" data-dismiss="modal" class="btn btn-simple">Cancel</button>
                            <button type="submit" class="btn btn-primary">@if($resident->is_flagged == false) Save @else Yes, remove @endif</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>