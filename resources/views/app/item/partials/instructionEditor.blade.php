<form method="POST" action="{{route('app.item.storeSingle', $item->id)}}" autocomplete="off">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-12">
            <h3 class="mt-4 float-left">Booking Instructions</h3>
            <button type="submit" class="btn btn-primary btn-round btn-sm mt-4 float-right">Save Changes</button>
        </div>
        <div class="col-12">
            <div class="html_editor_wrap">
                <div data-name="booking_instructions" class="_full_html_editor _html_content">{!!$item->booking_instructions!!}</div>
            </div> 
        </div>
    </div>
</form>