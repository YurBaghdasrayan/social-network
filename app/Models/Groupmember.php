<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Groupmember extends Model
{
    use HasFactory;

    protected $table = "group_members";

    protected $guarded = [];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function groupmembers()
    {
        return $this->belongsTo(Group::class,'group_id');
    }

}
