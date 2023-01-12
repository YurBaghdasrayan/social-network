<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Friend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user) {
            return response()->json([
                'success' => true,
                $user
            ], 200);
        } else {
            return response()->json([
                'success' => false,
            ], 422);
        }
    }

    public function changeStatus(Request $request)
    {
        $checkStatus = User::where('id', auth()->user()->id)->get();
        if ($checkStatus[0]->last_seen == 'online') {
            $checkStatus = User::where('id', auth()->user()->id)->update(['last_seen' => 'offline']);
            if ($checkStatus) {
                return response()->json([
                    'success' => true,
                    'message' => 'status changed online successfully'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ], 422);
            }
        } else {
            $checkStatus = User::where('id', auth()->user()->id)->update(['last_seen' => 'online']);
            if ($checkStatus) {
                return response()->json([
                    'success' => true,
                    'message' => 'status changed offline successfully'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ], 422);
            }
        }
    }

    public function userOnlineStatus($id)
    {
        $data = User::where('id', $id)->get();

        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'user with this id not found'
            ], 200);
        } else {
            $minute = \Carbon\Carbon::parse($data[0]->last_seen)->diffForHumans();
            return response()->json([
                'success' => true,
                'last seen' => $minute
            ]);
        }
    }

    public function logout()
    {
        $user = Auth::user()->token();
//        if ($user) {
//            $offline = User::where('id', auth()->user()->id)->update(['last_seen' => Carbon::now()]);
//            $offlineUser = User::where('id', auth()->user()->id)->get();
//            $stringToTime = strtotime($offlineUser[0]->last_seen);
//            $minute = \Carbon\Carbon::parse($stringToTime)->diffForHumans();
//        }

        $user->revoke();
        if ($user) {
            return response()->json([
                'status' => true,
                'message' => 'this user is offline',
//                'user' => $minute
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'something was wrong'
            ], 422);
        }
    }

    public function profile()
    {
        $auth = User::where('id', auth()->user()->id)->with('post.comment')->get();
        if ($auth) {
            return response()->json([
                'status' => true,
                'message' => 'user profile',
                'data' => $auth
            ], 200);
        } else
            return response()->json([
                'status' => false,
                'message' => 'something was wrong'
            ], 422);
        }

    public function OtherProfile($id)
    {
        $auth = User::where('id', $id)->with('post.comment')->get();
        if ($auth) { 
            return response()->json([
                'status' => true,
                'message' => 'user profile',
                'data' => $auth
            ], 200);
        } else
            return response()->json([
                'status' => false,
                'message' => 'something was wrong'
            ], 422);
    }



    public function singlePageUser($id){
        $user = User::where('id', $id)->get();

        $post = Post::where('user_id',$id)->with('images','user','comment', 'comment.commmentlikeAuthUser',
            'comment.comentreply','comment.comentreply.user','comment.comentreply.commentsreplylikeAuthUser',
            'comment.comentreply.comentreplyanswer',
            'comment.comentreply.comentreplyanswer.user'
        )
            ->withCount('postlikes', 'comment')
            ->with([
                'comment' => function ($query) {
                    $query->withCount('commmentlike')->withCount('comentreply');
                },
                'comment.comentreply' => function ($query) {
                    $query->withCount('commentsreplylike')->withCount('comentreplyanswer');
                }
                ,
                'comment.comentreply.comentreplyanswer' => function ($query) {
                    $query->withCount('replyanswerlike');
                },

            ])
            ->orderBy('id', 'desc')
            ->simplepaginate(15);

        $friend = Friend::where('sender_id',auth()->user()->id)->where('receiver_id', $id)
            ->orWhere('receiver_id', auth()->user()->id)->where('sender_id', $id)->get();


        return response()->json([
           'status' => true,
           'data' => $user,
            'post' => $post,
            'friend' => $friend
        ],200);
    }


    public function UpdatePhotoAndBagraundPhoto(Request $request){

        if(isset($request->avatar)){
            $avatar = $request->file('avatar');
            $destinationPath = 'uploads';
            $originalFile = time() . $avatar->getClientOriginalName();
            $avatar->move($destinationPath, $originalFile);
            $avatar = $originalFile;
            User::where('id', auth()->user()->id)->update([
                'avatar' => $avatar,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'photo updated',
                'photo' => $avatar
            ]);
        }
       elseif(isset($request->backraundPhoto)){
            $avatar = $request->file('backraundPhoto');
            $destinationPath = 'uploads';
            $originalFile = time() . $avatar->getClientOriginalName();
            $avatar->move($destinationPath, $originalFile);
            $avatar = $originalFile;
            User::where('id', auth()->user()->id)->update([
                'backraundPhoto' => $avatar,
            ]);
            return response()->json([
                'status' => true,
                'message' => 'backraund updated',
                'photo' => $avatar
            ]);
        }else{
            return  response([
                'status' => false,
                'message' =>  'inches uxarkel  vor mihatel uzumes updtae anes  normal tvyal uxarki kam  backraundPhoto kam avatar'
            ], 422);
        }
    }

}


