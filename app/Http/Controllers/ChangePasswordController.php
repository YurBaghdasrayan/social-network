<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{

    /**
     * @OA\Post(
     * path="api/change-password",
     * summary="User Send Password for change",
     * description="User Send Password for change",
     * operationId="User Send Password for change",
     * tags={"Change Password"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Send Password for change",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="newpassword", type="password", format="password", example="1111111"),
     *          @OA\Property(property="oldpassword", type="password", format="password", example="1111111"),
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

    public function changePassword(Request $request)
    {
        $newpassword = $request->newpassword;
        $oldpassword = $request->oldpassword;
        $user = User::where('id', auth()->user()->id)->first();
        $hash_check = Hash::check($request->oldpassword, $user->password);
        if ($hash_check) {
            $passwordchange = User::where('password', $user->password)->update([
                'password' => Hash::make($newpassword),
            ]);
            if ($passwordchange) {
                return response()->json([
                    'success' => true,
                    'message' => 'password updated'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ], 200);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'password not found'
            ], 200);
        }
    }

    /**
     * @OA\Post(
     * path="api/change-username",
     * summary="User Send Username for change",
     * description="User Send Username for change",
     * operationId="User Send Username for change",
     * tags={"Change Username"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Send Username for change",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="username", type="text", format="text", example="example"),
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


    public function ChangeUsername(Request $request)
    {


        $username = $request->username;
        if (isset($username)) {

                $changeUsername = User::where('id', auth()->user()->id)->update(['username' => $username]);
            if ($changeUsername) {
                return response()->json([
                    'success' => true,
                    'message' => 'your username successfully changed'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong please try again'
                ], 422);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' =>  'username required'
            ], 422);
        }
    }
}
