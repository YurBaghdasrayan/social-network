<?php

namespace App\Http\Controllers;

use App\Events\CommentNotification;
use App\Models\User;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class CommentController extends Controller
{

    /**
     * @OA\Get(
     * path="api/all-notifications",
     * summary="User all notifications",
     * description="User all notifications",
     * operationId="Users all notifications",
     * tags={"Notifications"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User all notifications",
     *    @OA\JsonContent(
     *               required={"true"},
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="User all notifications",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function index()
    {
        $notificationData = Notification::where(['receiver_id' => auth()->user()->id, 'notification_type' => 'new comment'])->get();
        if ($notificationData) {
            return response()->json([
                'status' => true,
                'message' => 'this notifications',
                'data' => $notificationData
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
     * path="api/viewed-notification",
     * summary="User viewed Comments",
     * description="User viewed Comments",
     * operationId="Users viewed Comments",
     * tags={"Notifications"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User viewed Comments",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="notification_id", type="integer",format="text", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="User viewed Comments",
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
     * path="api/comment",
     * summary="User Create Comments",
     * description="User Create Comments",
     * operationId="Users Create Comments",
     * tags={"Comments"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Create Comments",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="comment", type="string",format="text", example="some comment"),
     *               @OA\Property(property="post_id", type="integer",format="text", example="1"),
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

        $comment = [
            'comment' => $request->comment,
            'post_id' => $request->post_id,
            'user_id' => auth()->user()->id,
        ];
        $user = auth()->user();
        DB::beginTransaction();

        $CreatComment = Comment::create($comment);

        DB::commit();

        if ($CreatComment) {
            event(new CommentNotification($comment, $user));
            return response()->json([
                'success' => true,
                'message' => 'comment added successfully',
                'user' => auth()->user(),
                'data' => $comment
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong'
            ], 422);
        }
    }
}




