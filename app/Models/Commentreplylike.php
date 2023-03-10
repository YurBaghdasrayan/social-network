<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commentreplylike extends Model
{
    use HasFactory;

    protected $table = "comments_reply_like";

    protected $guarded = [];

    public function commentsreply()
    {
        return $this->belongsTo(Comentreply::class,'comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
