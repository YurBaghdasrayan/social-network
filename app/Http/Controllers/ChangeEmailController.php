<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Models\Changeemail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;

class ChangeEmailController extends Controller
{
    /**
     * @OA\Post(
     * path="api/change-email",
     * summary="User Send Email for change",
     * description="User Send Email for change",
     * operationId="User Send Email for change",
     * tags={"Change Email"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Send Email for change",
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

    public function addEmail(Request $request)
    {

        $rules=array(
            'email' => 'required|unique:users',

        );
        $validator=Validator::make($request->all(),$rules);
        if($validator->fails())
        {
            return $validator->errors();
        }


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

//            Mail::to($request->email)->send(new SendMail($details));

            return response()->json([
                'success' => true,
                'message' => 'email change code sent to your phone ',
                'varify' => $randomNumber
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
     * path="api/update-email",
     * summary="User Send code for change",
     * description="User Send code for change",
     * operationId="User Send code for change",
     * tags={"Change Email"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Send code for change",
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


    public function UpdateEmail(Request $request)
    {
        $code = $request->random_int;
        $user_id = $request->user_id;

        $user_email = Changeemail::where('random_int', $code)->get();

        if (!$user_email->isEmpty()) {
            $number = User::where('id', $user_id)->update([
                'email' => $user_email[0]->email
            ]);
            $delete = Changeemail::where(['user_id' => $user_id])->delete();

            if ($delete) {
                return response()->json([
                    'success' => true,
                    'message' => 'your email successfully changed'
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
