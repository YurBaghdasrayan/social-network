<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comentreply;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Post;
use App\Models\Replyanswer;
use App\Events\ReplyAnswerNotification;
use Illuminate\Support\Facades\DB;

use App\Models\User;


class ReplyAnsverController extends Controller
{

    /**
     * @OA\Post(
     * path="api/replyanswer_viewed-notification",
     * summary="User viewed Reply Answer Comments",
     * description="User viewed Reply Answer Comments",
     * operationId="Users viewed Reply Answer Comments",
     * tags={"Notifications"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Users viewed Reply Answer Comments",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="notification_id", type="integer",format="text", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="User viewed Reply Answer Comments",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function changeStatus(Request $request)
    {
        $deleteNotification = Notification::where('id', $request->notification_id)->update(['status' => false]);

        if ($deleteNotification) {
            return response()->json([
                'status' => true,
                'message' => 'this notice has been viewed'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'something was wrong'
            ], 422);
        }
    }

    /**
     * @OA\Post(
     * path="api/comment-reply-answer",
     * summary="User Create Comments Replys",
     * description="User Create Comments Replys",
     * operationId="User Create Comments Replys",
     * tags={"Comments"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Create Comments Reply",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="reply", type="string",format="text", example="some comment"),
     *               @OA\Property(property="reply_id", type="integer",format="text", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="User Create Comments",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function store(Request $request)
    {

        $CommentReply = [
            'user_id' => auth()->user()->id,
            'reply' => $request->reply,
            'reply_id' => $request->reply_id
        ];

        $user = auth()->user();


        DB::beginTransaction();
        
        $notificationPost = Comentreply::where('id', $request->reply_id)->with('user')
            ->first();

        $create = Replyanswer::create($CommentReply);

        DB::commit();

        if ($create) {
            event(new ReplyAnswerNotification($notificationPost, $user));
            return response()->json([
                'success' => true,
                'message' => 'answer to answer reply successfully created',
                'data' => $notificationPost,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong',
            ], 422);
        }
    }
}
