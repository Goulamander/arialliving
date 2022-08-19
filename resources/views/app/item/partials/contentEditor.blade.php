<form method="POST" action="{{route('app.item.storeSingle', $item->id)}}" autocomplete="off">
    {{ csrf_field() }}
    <div class="row">
        <div class="col-12">
            <h3 class="mt-4 float-left">Item Content</h3>
            <button type="submit" class="btn btn-primary btn-round btn-sm mt-4 float-right">Save Changes</button>
        </div>
        <div class="col-12">
            <div class="html_editor_wrap">
                <div data-name="description" class="_full_html_editor _html_content">{!!$item->description!!}</div>
            </div> 
        </div>
    </div>
</form>