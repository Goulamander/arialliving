<div id="mod-refund" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog" style="max-width:500px">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <h4>Please confirm the Booking Fee Refund</h4>
                <div class="form-footer mt40 mb10">
                    <form autocomplete="off" class="mod-form" action="" method="POST">
                        {{ csrf_field() }}
                        <div class="form-group checkbox mb-4 mt-3">
                            <div class="checkbox">
                                <input type="checkbox" name="refund_full_amount" id="refund" value="1" data-toggle="collapse" data-target="#refundAmountForm" aria-controls="refundAmountForm" checked>
                                <label for="refund">Full Refund</label>
                            </div>
                        </div>
                        <div id="refundAmountForm" class="collapse">
                            <div class="form-group">
                                <label class="control-label">Enter the refund amount</label>
                                <input type="number" name="amount" class="form-control" min="0" required>
                            </div>
                            <div class="form-group">
                                <label class="control-label">Notes</label>
                                <textarea class="form-control" name="notes"></textarea>
                            </div>
                        </div>
                        <div class="modal-form-footer text-right mt-3">
                            <button type="button" data-dismiss="modal" class="btn btn-simple">Cancel</button>
                            <button type="submit" class="btn btn-brand">Proceed Refund</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>