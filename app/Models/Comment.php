<?php

namespace App\Models;

use App\Models\User;
use App\Models\Booking;
use App\Models\BookableItem;
use App\Models\Comment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

use Carbon\Carbon;
use Auth;

class Comment extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'type',
        'comment',
        'resident_id',
        'booking_id',
        'building_id',
        'bookable_item_id'
    ];


    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [];


    // Comment types
    public static $TYPE_COMMENT = 1;
    public static $TYPE_COMMENT_REPLY = 2;


    /***********************************************************************/
    /************************* ELOQUENT RELATIONSHIPS ***********************/
    /***********************************************************************/

    /**
     * User (comment owner)
     * @return App\Models\User
     */
    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

    /**
     * Resident
     * @return App\Models\Resident
     */
    public function resident()
    {
        return $this->belongsTo('App\Models\User', 'id', 'resident_id');
    }

    /**
     * Booking
     * @return App\Models\Booking
     */
    public function booking()
    {
        return $this->belongsTo('App\Models\Booking', 'id', 'booking_id');
    }

    /**
     * Building
     * @return App\Models\Building
     */
    public function building()
    {
        return $this->belongsTo('App\Models\Building', 'id', 'building_id');
    }

    /**
     * Bookable Item
     * @return App\Models\BookableItem
     */
    public function bookableItem()
    {
        return $this->belongsTo('App\Models\BookableItem', 'id', 'bookable_item_id');
    }

    /**
     * Comment Replies
     * @return App\Models\Comment
     */
    public function replies()
    {
        return $this->belongsToMany('App\Models\Comment', 'comment_replies', 'comment_id', 'reply_comment_id');
    }

    /**
     * Parent Comment of a reply comment
     * @return App\Models\Comment
     */
    public function parent_comment() {
        return $this->hasOne('App\Models\CommentReply');
    }





    /***********************************************************************/
    /******************************** SCOPES *******************************/
    /***********************************************************************/



	/**
	 * Exclude the replies
	 *
	 * @param
	 * @return
	 */
	public function scopeExcludeReplies($query)
	{
		return $query->where('type', '!=', self::$TYPE_COMMENT_REPLY);
	}



    /***********************************************************************/
    /**************************  PUBLIC METHODS  ***************************/
    /***********************************************************************/


    /**
	 * Get a Comment
	 * 
	 * @param int CommentId
     * 
	 * @return 
	 */
	public function get_comment($comment_id) {

        $comment = Comment::where('id', $comment_id)
            ->with('user:id,first_name,last_name,role_id')
            ->with('replies:id,user_id,comment,created_at,updated_at', 'replies.user:id,first_name,last_name,role_id')
            ->first();

        if($comment)
        {
            $comment->user->name = $comment->user->fullName();
            
            // Extend the comments Obj.
            $comment->created_day  = Carbon::parse($comment->created_at)->isToday() ? 'Today' : Carbon::parse($comment->created_at)->format('l');
            $comment->created_date = dateFormat($comment->created_at);
            $comment->created_time = timeFormat($comment->created_at);
            // convert markdown to html.
            $comment->comment_html = convertMarkdown($comment);
            $comment->can_edit = ($comment->user_id && $comment->user_id == Auth::user()->id) ? true : false;

            // add the parent_comment_id if this is a reply
            if($comment->type == self::$TYPE_COMMENT_REPLY) {
                $comment->parent_comment_id = $comment->parent_comment->reply_comment_id;
                // update comment date
                $comment->created_date = dateFormat($comment->created_at, 'M d');
            }

            // replies
            if( ! $comment->replies->isEmpty() ) 
            {
                foreach($comment->replies as $reply) 
                {
                    // convert markdown to html.
                    $reply->comment_html = convertMarkdown($reply);
                    $reply->created_date = dateFormat($reply->created_at, 'M d');
                    $reply->created_time = timeFormat($reply->created_at);

                    $reply->can_edit = ($reply->user_id && $reply->user_id == Auth::user()->id) ? true : false;
                }
            }
            
        }	

        return $comment;
    }


    /**
	 * Get Comments
	 * 
     * @param array Query where
	 * @param int Query offset
     * @param int Query limit
     * 
	 * @return
	 */
    public function get_comments($where, $offset, $limit) {

        $comments = Comment::where($where)
            ->with('user:id,first_name,last_name,role_id')
            ->with('replies:id,user_id,comment,created_at,updated_at', 'replies.user:id,first_name,last_name,role_id')
            ->excludeReplies()
            ->orderBy('comments.created_at', 'desc')
            ->offset($offset)
            ->take($limit)
            ->get();

        if($comments) 
        {
            // Extend the comments Obj.
            foreach($comments as $comment) 
            {
                $comment->created_day  = Carbon::parse($comment->created_at)->isToday() ? 'Today' : Carbon::parse($comment->created_at)->format('l');
                $comment->created_date = dateFormat($comment->created_at);
                $comment->created_time = timeFormat($comment->created_at);
                // convert markdown to html.
                $comment->comment_html = convertMarkdown($comment);
                $comment->can_edit = ($comment->user_id && $comment->user_id == Auth::user()->id) ? true : false;

                // replies
                if( ! $comment->replies->isEmpty() ) 
                {
                    foreach($comment->replies as $reply) 
                    {
                        // convert markdown to html.
                        $reply->comment_html = convertMarkdown($reply);
                        $reply->created_date = dateFormat($reply->created_at, 'M d');
                        $reply->created_time = timeFormat($reply->created_at);
                        $reply->can_edit = ($reply->user_id && $reply->user_id == Auth::user()->id) ? true : false;
                    }
                }

            }
        }	

        return $comments;
    }



    /**
     * Get cleaned (plain text) version of comment for the Notifications
     */
    public function getCommentAsText() {
        $HTML = markdown($this->comment);
        return strip_tags($HTML);
    }


    /**
     * Get the details of the comment parent
     */
    public function getParent() {

        $data = [];

        // Booking
        if($this->booking) {
            $data = [
                'id'     => $this->booking->id,
                // 'title'  => $this->job->title,
                // 'number' => $this->job->getNumber(),
                'type'   => 'booking'
            ];
        }
        else if($this->building) {
            $data = [
                'id'     => $this->building->id,
                // 'title'  => $this->enquiry->title(),
                // 'number' => $this->enquiry->getNumber(),
                'type'   => 'building'
            ];
        }
        else if($this->bookableItem) {
            $data = [
                'id'     => $this->bookableItem->id,
                // 'title'  => $this->invoice->getNumber(),
                // 'number' => $this->invoice->getNumber(),
                'type'   => 'bookableItem'
            ];
        }
        else if($this->resident) {
            $data = [
                'id'     => $this->resident->id,
                // 'title'  => $this->customer->name,
                // 'number' => $this->customer->getNumber(),
                'type'   => 'resident'
            ];
        }

        return json_decode(json_encode($data));
    }


}
