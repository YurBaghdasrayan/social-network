<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    use HasFactory;

    protected $table = ('chat');

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function forusers()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }


}
