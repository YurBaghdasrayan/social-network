<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comentreply;
use App\Models\Comment;
use App\Models\Post;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use App\Events\CommentReplyNotification;


class CommentReplyController extends Controller
{

    /**
     * @OA\Post(
     * path="api/reply_viewed-notification",
     * summary="User viewed Reply Comments",
     * description="User viewed Reply Comments",
     * operationId="Users viewed Reply Comments",
     * tags={"Notifications"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User viewed Reply Comments",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="notification_id", type="integer",format="text", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="User viewed Reply Comments",
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
     * path="api/comment-reply",
     * summary="User Create Comments Reply",
     * description="User Create Comments Reply",
     * operationId="User Create Comments Reply",
     * tags={"Comments"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Create Comments",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="comment_reply", type="string",format="text", example="some comment"),
     *               @OA\Property(property="comment_id", type="integer",format="text", example="1"),
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
            'comment_id' => $request->comment_id,
            'user_id' => auth()->user()->id,
            'comment_reply' => $request->comment_reply,
        ];

        $user = auth()->user();

        DB::beginTransaction();

        $notificationPost = Comment::where('id', $request->comment_id)->with('user')
            ->first();

        $create = Comentreply::create($CommentReply);


        DB::commit();

        if ($create) {
            event(new CommentReplyNotification($notificationPost,$user));
            return response()->json([
                'success' => true,
                'message' => 'comment reply successfully created',
                'data' => $notificationPost,
                'user' => auth()->user(),
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong',
            ], 422);
        }
    }
}
