<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'group';
    protected $guarded = [];

    public function posts()
    {
        return $this->hasMany(Post::class, 'group_id');
    }

    public function groupmembers()
    {
        return $this->hasMany(Groupmember::class);
    }

    public function receivergroup()
    {
        return $this->belongsto(User::class, 'receiver_id');
    }



    public function UserGroup()
    {
        return $this->belongsto(User::class, 'user_id');
    }
}
