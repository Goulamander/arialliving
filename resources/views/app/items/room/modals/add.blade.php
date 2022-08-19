
<div id="mod-item-room" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content" style="overflow:visible">
            <div class="modal-header">
                <h3>Create New Room/Area Item</h3>
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{route('app.item.store', 'room')}}" autocomplete="off" data-reset="true">
                    {{ csrf_field() }}
                    <ul class="nav nav-modal mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="mod-room-item-details-tab" data-toggle="pill" href="#mod-room-item-details" role="tab">Item details</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="mod-room-item-hours-tab" data-toggle="pill" href="#mod-room-item-hours" role="tab">Office Hours</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div id="mod-room-item-details" class="tab-pane fade show active" role="tabpanel" aria-labelledby="mod-room-item-details-tab">
                        @php $fields = json_decode(json_encode(\App\Models\BookableItem::form_room_fields())); @endphp

                        @foreach($fields as $key => $field)
                            {!! App\Helpers\FormHelper::getFields($key, $field) !!}
                        @endforeach
                        </div>
                        <div id="mod-room-item-hours" class="tab-pane fade" role="tabpanel" aria-labelledby="mod-room-item-hours-tab">
                            <h4 class="mt-4">Office Hours</h4>
                            <p>By default this item uses the attached build's office hours.</p>
                            <div class="checkbox">
                                <input type="checkbox" id="room-item-ignore-office-hours" name="ignore_office_hours" value="1">
                                <label for="room-item-ignore-office-hours">Allow residents to book this item outside of office hours</label>
                            </div>
                            <div class="checkbox">
                                <input type="checkbox" id="room-item-office-hours" name="set_custom_hours" data-toggle="collapse" data-target="#room-item-office-hours-tab" aria-controls="room-item-office-hours-tab" value="1">
                                <label for="room-item-office-hours">Set custom Office Hours?</label>
                            </div>
                            <div id="room-item-office-hours-tab" class="collapse pt-4">
                                @php $fields = json_decode(json_encode(\App\Models\BookableItem::$office_hours_fields)); @endphp
                                
                                @foreach($fields as $key => $field)
                                    {!! App\Helpers\FormHelper::getFields($key, $field) !!}
                                @endforeach
                            </div>
                        </div>
                    </div>


                    <div class="modal-form-footer text-right mt-3">
                        <button type="button" data-dismiss="modal" class="btn btn-simple modal-close">Cancel</button>
                        <button type="submit" class="btn btn-primary modal-close">Next</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
