<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Postlikes;
use App\Models\Commentslike;
use App\Models\Commentreplylike;
use App\Models\Replyanswerlike;

class PostLikesController extends Controller
{

    /**
     * @OA\Post(
     * path="api/post-likes",
     * summary="User Likes Posts",
     * description="User Likes Posts",
     * operationId="Users Likes Posts",
     * tags={"Post Likes"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Users Likes Posts",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="post_id", type="integer", format="number", example="1"),
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
        $likesExist = Postlikes::where(['post_id' => $request->post_id, 'user_id' => auth()->user()->id])->get();


        if ($likesExist->isEmpty()) {
            $PostLikes = [
                'post_id' => $request->post_id,
                'user_id' => auth()->user()->id
            ];
            $createlikes = Postlikes::create($PostLikes);

            if ($createlikes) {
                return response()->json([
                    'success' => true,
                    'message' => 'you have successfully liked the post'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ], 422);
            }
        } else {
            $delete = Postlikes::where(['post_id' => $request->post_id, 'user_id' => auth()->user()->id])->delete();

            if ($delete) {
                return response()->json([
                    'success' => true,
                    'message' => 'your like to take off'
                ], 200);
            }
        }
    }

    /**
     * @OA\Post(
     * path="api/comment-likes",
     * summary="User Comments like",
     * description="User Comments like",
     * operationId="Users Comments like",
     * tags={"Comments Likes"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Users Comments like",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="comment_id", type="integer", format="number", example="1"),
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

    public function commentStore(Request $request)
    {

        $likesExist = Commentslike::where(['comment_id' => $request->comment_id, 'user_id' => auth()->user()->id])->get();

        if ($likesExist->isEmpty()) {
            $PostLikes = [
                'comment_id' => $request->comment_id,
                'user_id' => auth()->user()->id
            ];
            $createlikes = Commentslike::create($PostLikes);

            if ($createlikes) {
                return response()->json([
                    'success' => true,
                    'message' => 'you have successfully liked the comment'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ], 422);
            }
        } else {
            $delete = Commentslike::where(['comment_id' => $request->comment_id, 'user_id' => auth()->user()->id])->delete();

            if ($delete) {
                return response()->json([
                    'success' => true,
                    'message' => 'your like to take off'
                ], 200);
            }
        }
    }

    /**
     * @OA\Post(
     * path="api/comment-reply-likes",
     * summary="User Comments reply like",
     * description="Users Comments reply like",
     * operationId="User Comments reply like",
     * tags={"Comments Reply Likes"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Users Comments reply like",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="comment_id", type="integer", format="number", example="1"),
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


    public function commentreplyStore(Request $request)
    {

        $likesExist = Commentreplylike::where(['comment_id' => $request->comment_id, 'user_id' => auth()->user()->id])->get();

        if ($likesExist->isEmpty()) {
            $PostLikes = [
                'comment_id' => $request->comment_id,
                'user_id' => auth()->user()->id
            ];
            $createlikes = Commentreplylike::create($PostLikes);

            if ($createlikes) {
                return response()->json([
                    'success' => true,
                    'message' => 'you have successfully liked the comment reply'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ], 422);
            }
        } else {
            $delete = Commentreplylike::where(['comment_id' => $request->comment_id, 'user_id' => auth()->user()->id])->delete();

            if ($delete) {
                return response()->json([
                    'success' => true,
                    'message' => 'your like to take off'
                ], 200);
            }
        }
    }

    /**
     * @OA\Post(
     * path="api/reply-answer-likes",
     * summary="User reply answer like",
     * description="User reply answer like",
     * operationId="Users reply answer like",
     * tags={"Reply Answer like"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User reply answer like",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="comment_id", type="integer", format="number", example="1"),
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

    public function replyanswerStore(Request $request)
    {
        $data = User::where('id', auth()->user()->id)->with('commentlike')->get();

        $likesExist = Replyanswerlike::where(['comment_id' => $request->comment_id, 'user_id' => auth()->user()->id])->get();

        if ($likesExist->isEmpty()) {
            $PostLikes = [
                'comment_id' => $request->comment_id,
                'user_id' => auth()->user()->id
            ];
            $createlikes = Replyanswerlike::create($PostLikes);

            if ($createlikes) {
                return response()->json([
                    'success' => true,
                    'message' => 'you have successfully liked the reply answer'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ], 422);
            }
        } else {
            $delete = Replyanswerlike::where(['comment_id' => $request->comment_id, 'user_id' => auth()->user()->id])->delete();

            if ($delete) {
                return response()->json([
                    'success' => true,
                    'message' => 'your like to take off'
                ], 200);
            }
        }
    }
}
