<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Groupmember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupMembersController extends Controller
{

    /**
     * @OA\Get(
     * path="api/group-data",
     * summary="Group members data",
     * description="Group members data",
     * operationId="Groups members data",
     * tags={"Groups Members"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Group members data",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="id", type="integer",format="id", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Group members data",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */

    public function index($id)
    {
        $groupData = Groupmember::where('group_id', $id)->where('user_status', '=', null)->with('receiver')->get();

        if ($groupData) {
            return response()->json([
                'success' => true,
                'message' => 'this group members',
                'data' => $groupData,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong',
            ], 422);
        }
    }

    /**
     * @OA\Post(
     * path="api/add-group",
     * summary="User Send Request",
     * description="User Send Request",
     * operationId="Userss Send Request",
     * tags={"Groups"},
     * @OA\RequestBody(
     *    required=true,
     *    description="user sent a friend pressure request",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="receiver_id", type="integer",format="text", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="User Send Request",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function store(Request $request)
    {
        $user = User::where('id', auth()->user()->id)->with('sender')->get();
        $GroupMembersData = [
            'receiver_id' => $request->receiver_id,
            'sender_id' => auth()->user()->id,
            'user_status' => 'unconfirm',
            'group_id' => $request->group_id,
        ];

        DB::beginTransaction();

        $requesCount = Groupmember::where('receiver_id', $request->receiver_id)
            ->where('group_id', $request->group_id)->get();
        if ($requesCount->count() < 1) {
            $addMembers = Groupmember::create($GroupMembersData);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'you have a already sent request'
            ], 422);
        }

        $notificationMembers = Groupmember::where('receiver_id', $request->receiver_id)->with('receiver')
            ->get();

        DB::commit();

        if ($addMembers) {
            return response()->json([
                'success' => true,
                'message' => 'your request sended',
                'data' => $notificationMembers,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong',
            ]);
        }
    }

    /**
     * @OA\Post(
     * path="api/confirm-group-request",
     * summary="user accepts group request",
     * description="user accepts group request",
     * operationId="user accepts group request",
     * tags={"Groups"},
     * @OA\RequestBody(
     *    required=true,
     *    description="user accepts group request",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="sender_id", type="integer",format="text", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="user accepts group request",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function confirmRequest(Request $request)
    {
        $confirm = Groupmember::where('receiver_id', auth()->user()->id)
            ->where('sender_id', $request->sender_id)->with('sender')->get();

        if ($confirm->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'sender not found'
            ], 404);
        } else {
            $confirmSuccess = Groupmember::where('receiver_id', auth()->user()->id)
                ->where('sender_id', $request->sender_id)->update(['user_status' => 'true']);

            if ($confirmSuccess) {
                return response()->json([
                    'success' => true,
                    'message' => 'you have successfully accepted the request'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ], 422);
            }
        }
    }

    /**
     * @OA\Post(
     * path="api/cancel-group-request",
     * summary="user request canceled",
     * description="user request canceled",
     * operationId="userss request canceled",
     * tags={"Groups"},
     * @OA\RequestBody(
     *    required=true,
     *    description="user request canceled",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="sender_id", type="integer",format="text", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="user request canceled",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function cancelRequest(Request $request)
    {
        $cacnelRequest = Groupmember::where('receiver_id', auth()->user()->id)
            ->where('sender_id', $request->sender_id)->update(['user_status' => 'false']);

        if ($cacnelRequest) {
            return response()->json([
                'success' => true,
                'message' => 'request canceled'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong'
            ], 422);
        }
    }

    /**
     * @OA\Get(
     * path="api/leave-the-group",
     * summary="Leave Group ",
     * description="Leave Group",
     * operationId="Leave Group",
     * tags={"Groups"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Leave Group",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="receiver_id", type="integer",format="id", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Leave Group",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */

    public function leaveGroup(Request $request)
    {
        $cacnelRequest = Groupmember::where('receiver_id', auth()->user()->id)->delete();
        if ($cacnelRequest) {
            return response()->json([
                'success' => true,
                'message' => 'you have successfully left the group'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong'
            ], 422);
        }
    }

    /**
     * @OA\Post(
     * path="api/add-black-list",
     * summary="Black List ",
     * description="Black List",
     * operationId="Black Lists",
     * tags={"Groups"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Black List",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="user_id", type="integer",format="id", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Black List",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function BlackList(Request $request)
    {
        $blackList = Groupmember::where('receiver_id', $request->user_id)->update(['user_status' => 'black_list']);

        if ($blackList) {
            return response()->json([
                'success' => true,
                'message' => 'user successfully added to black list'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong'
            ], 200);
        }
    }
}
