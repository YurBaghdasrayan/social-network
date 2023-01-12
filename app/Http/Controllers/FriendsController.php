<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use App\Models\Friend;
use App\Models\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Events\FriendRequestEvent;

class FriendsController extends Controller
{
    public function index()
    {
        $data = Friend::where('receiver_id', auth()->user()->id)
            ->where('status', 'unconfirm')
            ->with('sender')
            ->get();

        $userData = [];

        foreach ($data as $datum) {
            $int = (int)$datum['sender_id'];

            $userss = User::where('id', $int)->get();
//            $userData[] = $userss;
        }

        event(new FriendRequestEvent($userss));
        if ($data) {
            return response()->json([
                'success' => true,
                'message' => 'sent you a request',
                'data' => $userss,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong'
            ]);
        }
    }

    public function AllFriends()
    {
        $data = Friend::where('receiver_id', auth()->user()->id)->where('status', 'true')->with('sender')->get();
        if ($data) {
            return response()->json([
                'success' => true,
                'message' => 'your friends',
                'data' => $data,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong'
            ]);
        }
    }


    /**
     * @OA\Post(
     * path="api/add-friends",
     * summary="User Send Request",
     * description="User Send Request",
     * operationId="Users Send Request",
     * tags={"Friends"},
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
     *    description="user sent a friend pressure request",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $user = User::where('id', auth()->user()->id)->with('sender')->get();
        $friendData = [
            'receiver_id' => $request->receiver_id,
            'sender_id' => auth()->user()->id,
            'status' => 'unconfirm'
        ];

        DB::beginTransaction();

        $requesCount = Friend::where('receiver_id', $request->receiver_id)
            ->where('sender_id', auth()->user()->id)->get();
        if ($requesCount->count() < 1) {
            $addFriends = Friend::create($friendData);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'you have a already sent request'
            ], 422);
        }

        $notificationFriends = Friend::where('receiver_id', $request->receiver_id)->with('receiver')
            ->get();

        DB::commit();

        if ($addFriends) {
            return response()->json([
                'success' => true,
                'message' => 'your request sended',
                'data' => $notificationFriends,
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
     * path="api/confirm-request",
     * summary="user accepts friend request",
     * description="user accepts friend request",
     * operationId="user accepts friend request",
     * tags={"Friends"},
     * @OA\RequestBody(
     *    required=true,
     *    description="user accepts friend request",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="sender_id", type="integer",format="text", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="user accepts friend request",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function confirmRequest(Request $request)
    {
        $confirm = Friend::where('receiver_id', auth()->user()->id)
            ->where('sender_id', $request->sender_id)->with('sender')->get();

        if ($confirm->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'sender not found'
            ], 404);
        } else {
            $confirmSuccess = Friend::where('receiver_id', auth()->user()->id)
                ->where('sender_id', $request->sender_id)->update(['status' => 'true']);

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
     * path="api/cancel-request",
     * summary="user request canceled",
     * description="user request canceled",
     * operationId="user request canceled",
     * tags={"Friends"},
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
        $cacnelRequest = Friend::where('receiver_id', auth()->user()->id)
            ->where('sender_id', $request->sender_id)->update(['status' => 'false']);

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
     * @OA\Post(
     * path="api/delete-friend",
     * summary="user removed from friends list",
     * description="user removed from friends list",
     * operationId="user removed from friends list",
     * tags={"Friends"},
     * @OA\RequestBody(
     *    required=true,
     *    description="user removed from friends list",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="sender_id", type="integer",format="text", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="user removed from friends list",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function deleteFriend(Request $request)
    {
        $cacnelRequest = Friend::where('receiver_id', auth()->user()->id)
            ->where('sender_id', $request->sender_id)->orwhere('receiver_id', $request->sender_id)->where('sender_id', auth()->user()->id)->delete();

        if ($cacnelRequest) {
            return response()->json([
                'success' => true,
                'message' => 'user successfully removed from your friends list'
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
     * path="api/friends-birth",
     * summary="get users birthday",
     * description="get users birthday",
     * operationId="get user birthday",
     * tags={"Friends"},
     * @OA\RequestBody(
     *    required=true,
     *    description="get users birthday",
     *    @OA\JsonContent(
     *               required={"true"},
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="get users birthday",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function friendsBirth(Request $request)
    {
        $user = Friend::where('sender_id', auth()->user()->id)
            ->orWhere('receiver_id', auth()->user()->id)
            ->with(['sender', 'receiver'])
            ->get();

        if (!$user->isEmpty()) {
            foreach ($user as $value) {
                $users = [];
                if ($value['sender']->id != auth()->user()->id) {
                    $users = [
                        'id' => $value['sender']->id
                    ];

                }
                if ($value['receiver']->id != auth()->user()->id) {
                    $users = [
                        'id' => $value['receiver']->id
                    ];
                }
                $today = Carbon::now();
                $date = today();

                $userBirth = User::where('id', $users)
                    ->whereMonth('date_of_birth', $today->month)
                    ->whereDay('date_of_birth', $today->day)
                    ->get();

                if (!$userBirth->isEmpty()) {
                    $usersbirthday[] = $userBirth;
                }

                $UsersData = User::where('id', $users)
                    ->where('day', $today->month)
                    ->whereBetween('mount', array($today
                        ->addDays(-5)->day, $today
                        ->addDays(5)->day))->get();

                if (!$UsersData->isEmpty()) {
                    $beetwen[] = $UsersData;


                    foreach ($UsersData as $userTime) {
                        $int = (int)$userTime['mount'];

                        $sum = $int - $today->day;

                        if (!$UsersData->isEmpty()){
                            $howMany[] = [
                                'user_id' => $userTime['id'],
                                'username' => $userTime['name'],
                                'between days' => $sum
                            ];
                        }else{
                            $howMany = [];
                        };
                    }
                }else{
                    $beetwen = [];
                    $howMany = [];
                }
                foreach ($UsersData as $userTime) {
                    $int = (int)$userTime['mount'];

                    $sum = $int - $today->day;

                    if (!$UsersData->isEmpty()){
                        $howMany[] = [
                            'user_id' => $userTime['id'],
                            'username' => $userTime['name'],
                            'between days' => $sum
                        ];
                    }else{
                        $howMany = [];
                    };

                }
            }
            return response()->json([
                'success' => true,
                'message' => 'success',
//                'data' => [
                 'beetwen'=>   $beetwen,
                   'howmany' =>  $howMany
//                ],
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'you donthave friends'
            ], 404);
        }
    }
}
