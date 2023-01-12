<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Replyanswer extends Model
{
    use HasFactory;

    protected $table = 'answers';
    protected $guarded = [];

    public function commentreply()
    {
        return $this->belongsTo(Comentreply::class);
    }

    public function user()
    {

        return $this->belongsTo(User::class, 'user_id');
    }



    public function replyanswerlike()
    {
        return $this->hasMany(Replyanswerlike::class, 'comment_id');
    }
}
