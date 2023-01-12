<?php

namespace App\Http\Controllers;

use App\Models\Changeemail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use Validator;


class AddEmailController extends Controller
{
    /**
     * @OA\Post(
     * path="api/send-email",
     * summary="User Send Email for add",
     * description="User Send Email for add",
     * operationId="User Send Email for add",
     * tags={"Add Email"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Send Email for add",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="email", type="email", format="email", example="test@gmail.com"),
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

    public function sendemail(Request $request)
    {

        $rules = array(
            'email' => 'min:3|max:64|unique:users',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        }

        if (isset(auth()->user()->email)) {
            return response()->json([
                'success' => false,
                'message' => 'you have email'
            ], 422);
        } else {
            $randomNumber = random_int(100000, 999999);
            $credentails = [
                'email' => $request->email,
                'user_id' => auth()->user()->id,
                'random_int' => $randomNumber
            ];

            $CreateEmail = Changeemail::create($credentails);

            $details = [
                'email' => $request->email,
                'verification_at' => $randomNumber,
            ];

            if ($CreateEmail) {

                Mail::to($request->email)->send(new SendMail($details));

                return response()->json([
                    'success' => true,
                    'message' => 'email change code sent to your phone ',
                    'verify' => $randomNumber
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
     * path="api/add-email",
     * summary="User Send code for add",
     * description="User Send code for add",
     * operationId="User Send code for add",
     * tags={"Add Email"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Send code for add",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="random_int", type="int", format="int", example="123456"),
     *          @OA\Property(property="user_id", type="int", format="int", example="8"),
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


    public function addemail(Request $request)
    {
        $code = $request->random_int;
        $user_id = $request->user_id;

        $user_email = Changeemail::where('random_int', $code)->get();

        if (!$user_email->isEmpty()) {

            $user = User::where('id', auth()->user()->id)->get();
            $number = User::where('id', auth()->user()->id)->update(['email' => $user_email[0]->email]);
            $delete = Changeemail::where(['user_id' => $user_id])->delete();

            if ($delete) {
                return response()->json([
                    'success' => true,
                    'message' => 'your email successfully added'
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'something was wrong'
                ]);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'something was wrong'
            ]);
        }
    }
}

