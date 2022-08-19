<div id="mod-edit-gallery" tabindex="-1" role="dialog" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content" style="overflow:visible">
            <div class="modal-header">
                <h3>Edit gallery</h3>
                <button type="button" data-dismiss="modal" aria-hidden="true" class="close modal-close">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <input type="file" class="_gallery" name="file" data-name="file" data-title="Add New Image" data-path="{{$item->galleryPath()}}" data-process-type="gallery-image" multiple="" style="height:100px"/>
                
                <h4 class="mb-0 mt-4">Gallery images</h4>
                <small>Tap & drag images to reorder</small>
                <hr>

                <div class="_image_gallery mt-4">
                    @if($item->thumbs)
                        @foreach($item->thumbs as $thumb)
                            <div class="_img" style="background-image: url({{Storage::url($thumb)}})" data-file="{{$thumb}}">
                                <button type="button" class="no-btn _delete">
                                    <i class="material-icons">delete</i>
                                </button>
                            </div>
                        @endforeach
                    @else
                        <span class="empty">No images upload yet</span>
                    @endif
                </div>

                <div class="modal-form-footer text-right mt-3">
                    <button type="button" data-dismiss="modal" class="btn btn-simple modal-close">Done</button>
                </div>
            </div>
        </div>
    </div>
</div>