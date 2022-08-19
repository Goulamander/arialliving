
<div id="mod-building" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog" style="max-width:900px">
        <div class="modal-content" style="overflow:visible">
            <div class="modal-header">
                <h3>Add new building</h3>
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">

                <form method="POST" action="{{route('app.building.create')}}" autocomplete="off">
                    {{ csrf_field() }}

                    <ul class="nav nav-modal mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="mod-building-profile-tab" data-toggle="pill" href="#mod-building-profile" role="tab" aria-controls="pills-home" aria-selected="true">Building Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="mod-building-hours-tab" data-toggle="pill" href="#mod-building-hours" role="tab" aria-controls="pills-profile" aria-selected="false">Office Hours</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div id="mod-building-profile" class="tab-pane fade show active" role="tabpanel" aria-labelledby="mod-building-profile-tab">
                            @php $fields = json_decode(json_encode(\App\Models\Building::$form_fields)); @endphp
                            
                            @foreach($fields as $key => $field)
                                {!! App\Helpers\FormHelper::getFields($key, $field) !!}
                            @endforeach
                        </div>
                        <div id="mod-building-hours" class="tab-pane fade" role="tabpanel" aria-labelledby="mod-building-hours-tab">
                            @php $fields = json_decode(json_encode(\App\Models\Building::$office_hours_fields)); @endphp
                            
                            @foreach($fields as $key => $field)
                                {!! App\Helpers\FormHelper::getFields($key, $field) !!}
                            @endforeach
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
