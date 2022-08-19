{{-- Comments --}}
<div class="tab-list comment-list">
    
    <div class="new">
        <input type="hidden" name="id" value=""/>
        <div class="mde-editor">
            <textarea class="comment" rows="1" name="comment" placeholder="Enter message"></textarea>
            <button type="button" name="add" class="btn btn-primary btn-sm pull-right">Post</button>
        </div>
    </div>

    @php $m = ''; @endphp
    @if($comments)
        @foreach($comments as $comment) 

            @if( $comment->created_date != $m )  
            <div class="comment-feed-date{{$comment->created_day == 'Today' ? ' today' : ''}}">
                <div class="w-bg">
                    <span class="day">{{$comment->created_day}}</span> 
                    {{$comment->created_date}}
                </div>
            </div>
            @endif
            <div class="comment" data-id="{{$comment->id}}">
                <div class="comment--head">
                    <span class="--user">{{$comment->user ? $comment->user->fullName() : 'Auto generated'}}</span>
                    <span class="--at">{{$comment->created_time}}</span>
                    <div class="--actions">
                        @if($comment->can_edit)
                        <div class="btn-group">
                            <button type="button" class="trigger" data-toggle="dropdown" aria-expanded="true">
                                <i class="material-icons">more_horiz</i>
                            </button>
                            <ul role="menu" class="dropdown-menu dropdown-menu-right">
                                <li><button type="button" name="edit" value="{{$comment->id}}">Edit</button></li>
                                <li><button type="button" name="delete" value="{{$comment->id}}">Delete</button></li>
                            </ul>
                        </div>
                        @endif
                        <button type="button" class="reply" name="reply" data-tippy-content="Reply"><i class="material-icons">reply</i></button>
                    </div>
                </div>
                <div class="comment--body">{!! $comment->comment_html !!}</div>
                @if($comment->replies) 
                    @foreach($comment->replies as $reply)
                        <div class="comment--reply" data-id="{{$reply->id}}">
                            <div class="comment--head">
                                <span class="--user">{{$reply->user ? $reply->user->name : 'Auto Generated'}}</span>
                                <span class="--at">{{$reply->created_date}}, {{$reply->created_time}}</span>
                                <div class="--actions">
                                    @if($reply->can_edit)
                                    <div class="btn-group">
                                        <button type="button" class="trigger" data-toggle="dropdown" aria-expanded="true">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </button>
                                        <ul role="menu" class="dropdown-menu dropdown-menu-right">
                                            <li><button type="button" name="edit" value="{{$reply->id}}">Edit</button></li>
                                            <li><button type="button" name="delete" value="{{$reply->id}}">Delete</button></li>
                                        </ul>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="comment--body">{!! $reply->comment_html !!}</div>
                        </div>
                    @endforeach
                @endif
            </div>
            @php $m = $comment->created_date; @endphp
        @endforeach
    @endif
</div>