<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';
    protected $guarded = [];

    public function sendernotification()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receivernotification()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

//    public function TypeId()
//    {
//        if ($this->notification_type = 'new comment') {
//            return $this->belongsTo(Comment::class, 'foreign_id');
//        }
//        if ($this->notification_type = 'new comment reply') {
//            return $this->belongsTo(Comentreply::class, 'foreign_id');
//        }
//        if ($this->notification_type = 'new comment reply answer') {
//            return $this->belongsTo(Replyanswer::class, 'foreign_id');
//        }
//    }
}
