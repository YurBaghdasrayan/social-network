<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comentreply extends Model
{
    use HasFactory;

    protected $table = 'comment_reply';
    protected $guarded = [];

    public function comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    public function comentreplyanswer()
    {
        return $this->hasMany(Replyanswer::class, 'reply_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function commentsreplylike()
    {
        return $this->hasMany(Commentreplylike::class, 'comment_id');
    }
    public function commentsreplylikeAuthUser()
    {
        return $this->hasMany(Commentreplylike::class, 'comment_id')->where('user_id', auth()->user()->id);
    }
}
