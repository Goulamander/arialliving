@extends('layouts.dashboard')

@section('title', $data->subject . ' | ' . config('app.name'))


@section('content')
    @include('layouts.messagesTemplate')


    <div class="panel">
        <div class="panel-header"></div>
        <div class="panel-body">

            <div class="row">
                <div class="col-sm-12 col-lg-6">
                    <div class="card">
                        <div class="header">
                            <h2>
                                <strong>{{ $data->subject }} {!! $data->getStatus() !!}</strong>
                            </h2>
                        </div>
                        <div class="body">
                            <form method="POST" autocomplete="off" action="{{ route('app.marketing-communications.update', $data->id) }}">
                                @csrf
                                <input name="_method" type="hidden" value="PUT">
                                @php $fields = json_decode(json_encode(\App\Models\MarketingCommunications::form_fields())); @endphp

                                @foreach($fields as $key => $field)
                                    {!! App\Helpers\FormHelper::getFields($key, $field, $data) !!}
                                @endforeach
                                <div class="form-group">
                                    <label class="control-label">Body</label>
                                    <div class="html_editor_wrap">
                                        <div data-name="body" class="_full_html_editor">{!! $data->body !!}</div>
                                    </div> 
                                </div> 
                                <div class="form-group _invite_resident checkbox mb-4 mt-3">
                                    <div class="checkbox">
                                        <input type="checkbox" id="status" name="status" class="" value="1"
                                            data-parsley-multiple="status" {{ $data->status ? 'checked' : '' }}>
                                        <label for="status">Send Email(SMS) to resident(s) on creation?</label>
                                    </div>
                                </div>
                                @if ($can_edit)
                                    <div class="clearfix"></div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="submit" name="store"
                                                class="btn btn-sm btn-primary float-right">Save changes</button>
                                        </div>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-6">
                    
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script type="text/javascript">
        /**
         * On select change
         */
        $(document).on('change', 'select[name=resident_levels], select[name=building_id]', function(e)  { // Validate confirm password form
            e.preventDefault();
            $.ajax({
                url: `{{route("app.marketing-communications.getResidentList")}}?${this.name}=${this.value}`,
                type: "GET",
                success: function (res) {
                    if(res.data && res.data.length > 0){
                        let options = '';
                        res.data.map(v => {
                            options += `<option value="${v.id}">${v.first_name} ${v.last_name}</option>`;
                        })
                        $('select[name=receiver').html(options);
                    }
                },
                fail: function (e) {
                    _errorResponse(e)
                }
            });
        });
    </script>
@endsection
