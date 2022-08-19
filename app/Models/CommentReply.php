<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;

class CommentReply extends Model
{
	
	/**
	 * The table associated with the model.
	 *
	 * @var string
	 */

	protected $table = 'comment_replies';
	
	public $timestamps = false;
	
	protected $fillable = [
		'comment_id', 
		'reply_comment_id'
	];



	/**
     * Comment
     * @return App\Models\Comment
     */
    public function comment() {
        return $this->belongsTo('App\Models\Comment');
	}
	
	/**
     * Comment
     * @return App\Models\Comment
     */
    public function parentComment() {
        return $this->belongsTo('App\Models\Comment', 'reply_comment_id', 'id');
    }


}
