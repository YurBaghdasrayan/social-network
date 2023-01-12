<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class HiddenAccountController extends Controller
{
    /**
     * @OA\Post(
     * path="api/hidden-account",
     * summary="User Send password for hide account",
     * description="User Send password for hide account",
     * operationId="User SenD password for hide account",
     * tags={"Hide Account"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Send password for hide account",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="password", type="password", format="password", example="123456"),
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

    public function hiddenAccount(Request $request)
    {
        $password = $request->password;
        $user = User::where('id', auth()->user()->id)->first();

        $hash_check = Hash::check($request->password, $user->password);
        if ($hash_check == true) {
            $user_account = User::where('id', auth()->user()->id)->update(['hidden_account' => 'close']);
            return response()->json([
                'success' => true,
                'message' => 'hide your account successfully'
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'wrong password'
            ], 422);
        }
    }

    /**
     * @OA\Delete(
     * path="api/users-delete/id",
     * summary="User Send password for delete account",
     * description="User Send password for delete account",
     * operationId="User SenD password for delete account",
     * tags={"Delete Account"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Send password for hide account",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="password", type="password", format="password", example="123456"),
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

    public function destroy(Request $request, $id)
    {
        $user = User::where('id', $id)->first();
        if ($user === null) {
            return response()->json([
                'success' => false,
                'message' => 'this account deleted'
            ], 422);
        } else {
            $hash_check = Hash::check($request->password, $user->password);
            if ($hash_check == true) {
                $user_account = User::where('id', $id)->delete();
                return response()->json([
                    'success' => true,
                    'message' => 'delete your account successfully'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'wrong password'
                ]);
            }
        }
    }
}
