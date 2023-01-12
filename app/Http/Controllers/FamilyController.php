<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Friend;

class FamilyController extends Controller
{

    /**
     * @OA\Post(
     * path="api/send-family-request",
     * summary="user send family request",
     * description="user send family request",
     * operationId="users send family request",
     * tags={"Family"},
     * @OA\RequestBody(
     *    required=true,
     *    description="user send family request",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="receiver_id", type="integer",format="text", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="user send family request",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function sendRequest(Request $request)
    {
        $sendEmail = Friend::where('sender_id', auth()->user()->id)
            ->where('receiver_id', $request->receiver_id)->get();

        if ($sendEmail->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong'
            ], 422);
        } else {
            $sendEmails = Friend::where('sender_id', auth()->user()->id)
                ->where('receiver_id', $request->receiver_id)->update(['family_status' => 'unconfirm']);

            if ($sendEmails) {
                return response()->json([
                    'success' => true,
                    'message' => 'your request successfully sent'
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
     * path="api/confirm-family-request",
     * summary="user accepts family request",
     * description="user accepts family request",
     * operationId="users accepts family request",
     * tags={"Family"},
     * @OA\RequestBody(
     *    required=true,
     *    description="user accepts family request",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="sender_id", type="integer",format="text", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="user accepts family request",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function confirmFamilyRequest(Request $request)
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
                ->where('sender_id', $request->sender_id)->update(['family' => 'true', 'family_status' => null]);

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
     * path="api/cancel-family-request",
     * summary="user request canceled",
     * description="user request canceled",
     * operationId="users request canceled",
     * tags={"Family"},
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


    public function cancelFamilyRequest(Request $request)
    {
        $cacnelRequest = Friend::where('receiver_id', auth()->user()->id)
            ->where('sender_id', $request->sender_id)->update(['family' => 'false']);

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
     * path="api/delete-family-request",
     * summary="user removed from family list",
     * description="user removed from family list",
     * operationId="user removed from family list",
     * tags={"Family"},
     * @OA\RequestBody(
     *    required=true,
     *    description="user removed from family list",
     *    @OA\JsonContent(
     *               required={"true"},
     *               @OA\Property(property="sender_id", type="integer",format="text", example="1"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="user removed from family list",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */


    public function deleteFamily(Request $request)
    {
        $cacnelRequest = Friend::where('receiver_id', auth()->user()->id)
            ->where('sender_id', $request->sender_id)
            ->where('family', true)->update(['family' => 'false']);

        if ($cacnelRequest) {
            return response()->json([
                'success' => true,
                'message' => 'user successfully removed from your family list'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong'
            ], 422);
        }
    }
}
