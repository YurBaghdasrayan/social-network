<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Passport\HasApiTokens;
use PHPUnit\TextUI\XmlConfiguration\Group;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'datetime',
    ];

    public function UserGroup()
    {
        return $this->hasMany(Group::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function comment()
    {
        return $this->hasMany(Comment::class);
    }

    public function commentsreply()
    {
        return $this->hasMany(Comentreply::class, 'user_id');
    }

    public function answers()
    {
        return $this->hasMany(Replyanswer::class);
    }

    public function post()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    public function replyanswerlike()
    {
        return $this->hasMany(Replyanswerlike::class);
    }

    public function replylike()
    {
        return $this->hasMany(Commentreplylike::class);
    }

    public function commentlike()
    {
        return $this->hasMany(Commentslike::class);
    }

    public function receiver()
    {
        return $this->hasMany(Friend::class, 'receiver_id');
    }

    public function sender()
    {
        return $this->hasMany(Friend::class, 'sender_id');
    }

    public function receivergroup()
    {
        return $this->hasMany(Groupmember::class, 'receiver_id');
    }

    public function sendergroup()
    {
        return $this->hasMany(Groupmember::class, 'sender_id');
    }

    public function reciverchat()
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }

    public function senderchat()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }


}
