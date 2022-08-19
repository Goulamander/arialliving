<div id="mod-password-confirm" tabindex="-1" role="dialog" class="modal fade" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="overflow:visible">
            <div class="modal-header">
                <h3>Password Confirm</h3>
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <form class="jsSubmit" id="ConfirmPasswordForm" autocomplete="off" action="{{ route('app.resident.booking.confirmPassword', [\App\Models\BookableItem::$TYPE_LABEL[$item->type], $item->id]) }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Confirm your password" required autocomplete="off" />
                    </div>

                    <div class="modal-form-footer text-right mt-3">
                        <button type="button" data-dismiss="modal" class="btn btn-simple modal-close">Cancel</button>
                        <button type="submit" class="btn btn-primary modal-close">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
