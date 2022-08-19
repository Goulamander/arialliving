<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseClasses\Controller;

use Illuminate\Http\Request;
use App\Http\Requests\StoreCommentRequest;

use App\Models\Building;
use App\Models\Booking;
use App\Models\BookableItem;
use App\Models\User;
use App\Models\Role;

use App\Models\Comment;
use App\Models\CommentReply;

use App\Models\Notification;

use App\Events\EventComment;
use App\Events\EventCommentDelete;
use App\Events\EventCommentUpdate;

use Carbon\Carbon;
use Illuminate\Support\Str;

use Auth;
use Illuminate\Support\Facades\Log;
use HasEvents;


class CommentController extends Controller
{

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /*
    |--------------------------------------------------------------------------
    | Comments
    |--------------------------------------------------------------------------
    |
    */

    /**
     * Store / Update Comment
     *
     * @param  Illuminate\Http\CommentRequest $request
     * @return json Response Comment
     */

    public function storeComment(StoreCommentRequest $request) {

        $is_new = $request->id == 0 ? true : false;

        $commentParent = null;

        switch($request->data_type) 
        {
            case 'building':
                $commentParent = Building::where('id', $request->data_id)
                    ->withTrashed()
                    ->first();
                /**
                 * Permission check
                 *  - user must be assigned
                 */

                break;

            case 'booking':
                $commentParent = Booking::where('id', $request->data_id)
                    ->withTrashed()
                    ->first();
                /**
                 * Permission check
                 *  - user must be master_admin
                 */
                break;

            case 'item':
                $commentParent = BookableItem::where('id', $request->data_id)
                    ->withTrashed()
                    ->first();
                /**
                 * Permission check
                 *  - user must be assigned
                 */                
                break;

            case 'resident':
                $commentParent = User::where('id', $request->data_id)
                    ->withTrashed()
                    ->first();
                /**
                 * Permission check
                 *  - user must be master_admin
                 */                 
                break;
        }
         
        // If job is not found, return error message
        if (!$commentParent) {
            return response()->json([
                'message'  => 'Comment parent was not found.',
                'code'     => 400,
            ], 400);
        }

        $comment_id = $request->input('id', 0);
        
        $comment = $commentParent->comments()->updateOrCreate(['id' => $comment_id], [
            'user_id' => Auth::id(),
            'type'    => $request->input('type', 1),
            'comment' => $request->comment
        ]);


        // if reply add relation
        if( $is_new && $comment->type == Comment::$TYPE_COMMENT_REPLY ) {
            CommentReply::create([
                'comment_id' => $comment->id,
                'reply_comment_id' => $request->parent_comment_id
            ]);
        }

        
        if($comment) {
            return response()->json($this->getComment($comment->id), 200);
        }

        return response()->json([
            'message'    => 'Comment cannot be saved.',
            'code'       => 400
        ], 400);
    }



    /**
	 * Get a Comment
	 * 
	 * @param int $comment_id
	 * @return
	 */
	public function getComment($comment_id) {
        $comment = new Comment();
        return $comment->get_comment($comment_id);
    }




    /**
     * Delete Comment
     *
     * @param  Illuminate\Http\Request $request
     * @param  App\Models\Job $job
     * @return Response
     */

    public function deleteComment(Request $request) {
        
        $comment = Comment::find($request->input('comment_id'));

        if( !$comment ) {
            return response()->json([
                'message' => 'Comment cannot be found.',
                'code' => 400
            ], 400);
        }

        if( $comment->user_id != Auth::user()->id ) {
            return response()->json([
                'message' => 'You have no permission to remove this comment.',
                'code' => 400
            ], 400);
        }


        // delete the comment
        $comment->forceDelete();

        return response()->json([
            'message' => 'Comment removed.',
            'code' => 200
        ], 200);

    }

}
