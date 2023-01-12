<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $guarded = [];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function comentreply()
    {
        return $this->hasMany(Comentreply::class, 'comment_id');
    }

    public function commmentlike()
    {
        return $this->hasMany(Commentslike::class, 'comment_id');
    }

    public function commmentlikeAuthUser()
    {
        return $this->hasMany(Commentslike::class, 'comment_id')->where('user_id', auth()->user()->id);
    }


}
