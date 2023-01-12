<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Post;
use App\Models\Group;
use App\Models\Friend;
use App\Models\Notification;
use App\Models\Image;
use App\Events\PostNotification;

use Validator;

class PostController extends Controller
{
    public function index()
    {
        $postData = Post::where('user_id', auth()->user()->id)->with(['comment', 'comment.comentreply'])->get();

        return response()->json([
            'success' => true,
            'message' => 'product was successfully created',
            'data' => $postData
        ], 201);
    }

    public function allpost(Request $request){

        if(isset($request->group_id)){
            $post = Post::wheer('group_id', $request->group_id)->with('images','user','comment','PostLike', 'comment.commmentlikeAuthUser',
                'comment.comentreply','comment.comentreply.user','comment.comentreply.commentsreplylikeAuthUser',
                'comment.comentreply.comentreplyanswer',
                'comment.comentreply.comentreplyanswer.user'
            )->withCount('postlikes', 'comment')
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
        }else{
            $post = Post::with('images','user','comment', 'PostLike', 'comment.commmentlikeAuthUser',
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

                ])->orderBy('id', 'desc')
                ->simplepaginate(15);
        }



        return response()->json([
           'status' => true,
           'date' => $post
        ],200);
    }

    public function friendsPosts()
    {
        $data = Friend::where('receiver_id', auth()->user()->id)->where('status', 'true')->with('sender')->get();


        $usersPostData = [];
        foreach ($data as $datum) {
            $friendId = $datum['sender']['id'];
            $usersPost = User::where('id', $friendId)->with(['post.comment.comentreply'])->get();
            $usersPostData[] = $usersPost;
        }
        if (!$data->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => "these are your friend's posts",
                'data' => $usersPostData,
            ]);
        } else {
            return response()->json([
                'success' => false,
                "message" => "you dont have friends"
            ]);
        }
    }

    /**
     * @OA\Post(
     * path="api/post",
     * summary="User Create Posts",
     * description="User Create Posts",
     * operationId="Users Create Posts",
     * tags={"Post"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Users Create Posts",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="description", type="string", format="text", example="some description"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Wrong credentials response",
     *
     *
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $group = Group::where('id', $request->group_id)->with('posts')->get();

        $rules = array(
            'description' => 'min:1',
        );

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $validator->errors();
        }

        $fileNames = array_keys($request->allFiles());
        $data = $request->except($fileNames);
        $fileNames = array_keys($request->allFiles());
        $data['user_id'] = Auth::user()->id;

        $user = auth()->user();
        DB::beginTransaction();

        $post = Post::query()->create($data);

        if (count($fileNames)) {
            foreach ($fileNames as $fileName) {
                $images = $request->file($fileName);
                $test = [];
                $time = time();
                foreach ($images as $image) {

                    $destinationPath = 'uploads';
                    $originalFile = $time++ . $image->getClientOriginalName();
                    $image->move($destinationPath, $originalFile);

                    $test[] = $image->getPathname();
                    Image::create([
                        'post_id' => $post->id,
                        'image' => $originalFile
                    ]);
                }
            }
        }
        DB::commit();

        event(new PostNotification($data, $user));
        return response()->json([
            'success' => true,
            'message' => 'product was successfully created'
        ], 201);
    }
}
