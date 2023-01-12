<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('notification', function () {
    return true;
});

Broadcast::channel('comment-reply', function () {
    return true;
});

Broadcast::channel('posts', function () {
    return true;
});

Broadcast::channel('chat', function () {
    return true;
});

Broadcast::channel('friends', function () {
    return true;
});

Broadcast::channel('group', function () {
    return true;
});



