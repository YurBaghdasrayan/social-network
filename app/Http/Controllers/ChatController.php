<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Chat;
use App\Events\ChatNotification;


class ChatController extends Controller
{

    /**
     * @OA\Post(
     * path="api/chat",
     * summary="User Send message",
     * description="User send message",
     * operationId="Users send message",
     * tags={"Chat"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User send message",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="receiver_id", type="int", format="int", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function store(Request $request)
    {
        $get_chat = Chat::where('receiver_id', $request->receiver_id)->where('sender_id', auth()->user()->id)->first();
        if ($get_chat == null) {
            $get_chat2 = Chat::where('receiver_id', auth()->user()->id)->where('sender_id', $request->receiver_id)->first();
            if ($get_chat2 == null) {
                $room_id = time();
            } else {
                $room_id = $get_chat2->room_id;
            }
        } else {
            $room_id = $get_chat->room_id;
        }
        $fileNames = array_keys($request->allFiles());
        $data = $request->except($fileNames);
        $fileNames = array_keys($request->allFiles());
        if (count($fileNames)) {
            foreach ($fileNames as $fileName) {
                $image = $request->file($fileName);
                $destinationPath = 'public/uploads';
                $originalFile = time() . $image->getClientOriginalName();
                $data = [
                    'sender_id' => auth()->user()->id,
                    'receiver_id' => $request->receiver_id,
                    'messages' => $request->messages,
                    'notification' => 0,
                    'room_id' => $room_id,
                    'file' => $originalFile,
                ];
            }
        } else {
            $data = [
                'sender_id' => auth()->user()->id,
                'receiver_id' => $request->receiver_id,
                'messages' => $request->messages,
                'notification' => 0,
                'room_id' => $room_id,
            ];
        }
        $chat = Chat::create($data);

        if ($chat) {
            $chat_data = Chat::where("receiver_id", $request->receiver_id)->where("sender_id", auth()->user()->id)->get();
            foreach ($chat_data as $chat_datum)
                if ($chat_datum->receiver_id == auth()->id()) {
                    $chat_datum->receiver_id = $chat_data->sender_id;
                    $chat_datum->sender_id = auth()->id();
                }
            $user = auth()->user();
            $receiverUser = User::where('id', $chat_datum->receiver_id)->get();
            event(new ChatNotification($chat, $receiverUser, auth()->user()));
            return response()->json([
                "success" => true,
                "message" => "your message has been successfully sent",
                "data" => [
                    'message' =>  'message created'
//                    "message" => $chat,
//                    "sender" => $user,
//                    "receiver" => $receiverUser,
                ]
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong'
            ], 422);
        }
    }

    /**
     * @OA\Get(
     * path="api/rightsidechat",
     * summary="those users with whom you communicated",
     * description="those users with whom you communicated",
     * operationId="those userss with whom you communicated",
     * tags={"Chat"},
     * @OA\RequestBody(
     *    required=true,
     *    description="those users with whom you communicated",
     *    @OA\JsonContent(
     *       required={"required"},
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */

    public function RightSiteUsers(Request $request)
    {
        $usersChat = Chat::query()->where(function ($query) use ($request) {
            $query->where([
                'sender_id' => auth()->id(),
//                'sender_id' => $request->auth_user_id,
            ])->orWhere([
                'receiver_id' => auth()->id(),
//                'receiver_id' => $request->auth_user_id,
            ]);
        })
            ->with(['user', 'forusers'])
            ->orderByDesc('created_at')
            ->get()
            ->unique('room_id')
            ->toArray();

        $right_side_data = [];
        foreach ($usersChat as $item) {
            $review_count = collect($item);
            $user_name = auth()->id() == $item['forusers']['id'] ? $item['user']['name'] : $item['forusers']['name'];
            $receiver_id = auth()->id() == $item['forusers']['id'] ? $item['user']['id'] : $item['forusers']['id'];
            $user_image = auth()->id() == $item['forusers']['id'] ? $item['user']['avatar'] : $item['forusers']['avatar'];
            $user_surname = auth()->id() == $item['forusers']['id'] ? $item['user']['surname'] : $item['forusers']['surname'];

//            $user_name = $request->auth_user_id == $item['forusers']['id'] ? $item['user']['name'] : $item['forusers']['name'];
//            $receiver_id = $request->auth_user_id == $item['forusers']['id'] ? $item['user']['id'] : $item['forusers']['id'];
//            $user_image =$request->auth_user_id == $item['forusers']['id'] ? $item['user']['avatar'] : $item['forusers']['avatar'];
//            $user_surname = $request->auth_user_id == $item['forusers']['id'] ? $item['user']['surname'] : $item['forusers']['surname'];


            $image = $item['file'];
            $review = $review_count->sum('review');
            $messages = $item["messages"];
            $et_message = Chat::where('receiver_id', $receiver_id)->where('room_id', $item['room_id'])->sum('review');

          $rev = Chat::where('room_id',$item['room_id'])->where('receiver_id', auth()->user()->id)->sum('review');
//            $rev = Chat::where('room_id',$item['room_id'])->where('receiver_id', $request->auth_user_id)->sum('review');

            $hty = Chat::where('room_id', $item['room_id'])->latest()->first();
            $right_side_data[] = [
                'created_at' =>  $hty['created_at'],
                'user_name' => $user_name,
                'user_image' => $user_image,
                'messages' => $hty['message'],
                'image' => $hty['file'],
                'receiver_id' => $receiver_id,
                'review' => $rev,
                'count' => $et_message,
                'room_id' => $item['room_id'],
                'surname' => $user_surname,
            ];
        }


        return response()->json([
            'success' => true,
            'userschatdata' => $right_side_data,
        ], 200);
    }

    /**
     * @OA\Get(
     * path="api/chat{id}",
     * summary="user correspondence",
     * description="user correspondence",
     * operationId="user correspondence",
     * tags={"Chat"},
     * @OA\RequestBody(
     *    required=true,
     *    description="user correspondence",
     *    @OA\JsonContent(
     *       required={"required"},
     *     @OA\Property(property="id", type="int", format="int", example="1"),
     *
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function getUsersData(Request $request, $receiver_id)
    {
        $data = Chat::where(function ($query) use ($receiver_id) {
            $query->where([
                'sender_id' => auth()->id()
            ])->where('receiver_id', $receiver_id)
                ->orWhere([
                    'receiver_id' => auth()->id(),
                ])->where('sender_id', $receiver_id);
        })
            ->with(['user', 'forusers'])
            ->orderBy('id', 'ASC')
            ->get();

        $get_views = Chat::where('review', 1, 'room_id', $request->room_id, 'receiver_id', \auth()->id())
            ->get();


        $ids = [];
        foreach ($get_views as $review_id) {
            array_push($ids, $review_id->id);
            $update_views = Chat::where(['id' => $review_id->id])
                ->update(['review' => 0]);
        }

        $user = [];
        foreach ($data as $datum) {
            $user[] = User::where('id', $datum->receiver_id)->get();
        }

        if (isset($data[0])) {

            if ($data[0]->receiver_id == auth()->user()->id) {
                $reciver_id = $data[0]->sender_id;
            } else {
                $reciver_id = $data[0]->receiver_id;
            }

            return response([
                'success' => true,
                'sender' => auth()->user(),
                'message' => "chat between two users",
                'data' => $data,
                "receiver_user_data" => $user,
                "receiver_id" => $reciver_id,
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'user with this not found'
            ], 422);
        }
    }
}
