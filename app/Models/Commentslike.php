<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commentslike extends Model
{
    use HasFactory;

    protected $table = "comments_likes";

    protected $guarded = [];

    public function comment()
    {
        return $this->belongsTo(Comment::class,'comment_id');
    }

    public function commentLike()
    {
        return $this->belongsTo(Post::class,'post_id');
    }



    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
