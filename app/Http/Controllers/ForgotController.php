<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\RessetPassword;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RessetpasswordMail;
use Validator;
use GreenSMS\GreenSMS;


class ForgotController extends Controller
{
    
    public function index()
    {
        $data = Groupmembers::where('receiver_id', auth()->user()->id)->where('status', 'unconfirm')->with('sender')->get();
        if ($data) {
            return response()->json([
                'success' => true,
                'message' => 'sent you a request',
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
     * path="api/code-sending",
     * summary="User Send Email",
     * description="User Send Email",
     * operationId="User Send Email",
     * tags={"Forgot-password"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Send Email",
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

    public function send(Request $request)
    {
        if ($request->email) {
            $email_exist = User::where(['email' => $request->email,])->get();
            if (!$email_exist->isEmpty()) {
                $randomNumber = random_int(100000, 999999);
                $user_id = $email_exist[0]->id;
                $details = [
                    'name' => $email_exist[0]['name'],
                    'code' => $randomNumber,
                ];

            Mail::to($request->email)->send(new RessetpasswordMail($details));

                $code = RessetPassword::create([
                    "user_id" => $user_id,
                    "random_int" => $randomNumber,
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'code os sended to your email',
                    'code' => $randomNumber,
                ], 200);

            }else{
                return response()->json([
                    'status' => false,
                    'message' =>  'Senc Emailov User Goyutyun chuni  '
                ],422);
            }
        }
        if ($request->number) {
            $email_exist = User::where(['number' => $request->number,])->get();
            if (!$email_exist->isEmpty()) {
                $randomNumber = random_int(100000, 999999);
                $user_id = $email_exist[0]->id;
                $code = RessetPassword::create([
                    "user_id" => $user_id,
                    "random_int" => $randomNumber,
                ]);
                $number = $request->number;
                $call_number = preg_replace('/[^0-9]/', '', $number);
                try {
                    $client = new GreenSMS([
                        'user' => 'sadn',
                        'pass' => 'Dgdhh378qq',
                    ]);
                    $response = $client->sms->send([
                        'from' => 'Vatan',
                        'to' => $call_number,
                        'txt' => 'Ваш код потверждения' .' '. $randomNumber
                    ]);
                    User::where('number', $request->number)->where('verify_code', '!=', 1)->update([
                        'verify_code' =>  $randomNumber
                    ]);
                } catch (Exception $e) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Error in Green Smms',
                    ]);
                }



                return response()->json([
                    'success' => true,
                    'message' => 'code os sended to your number',
                    'code' => $randomNumber,
                ], 200);

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'this number does not exist'
                ], 404);
            }
        }
    }

    /**
     * @OA\Post(
     * path="api/restore-password",
     * summary="restore-password",
     * description="restore-password",
     * operationId="restore-password",
     * tags={"Forgot-password"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="random_int", type="integer", format="integer", example="551485"),
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


    public function CodeSend(Request $request)
    {
        $updatePassword = RessetPassword::where([
            'random_int' => $request->random_int,
        ])->get();
        if (!$updatePassword->isEmpty()) {
            return response()->json([
                'success' => true,
                'message' => 'you can continue',
                'user_id' => $updatePassword[0]->user_id
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'code is not right',
            ], 422);
        }
    }

    /**
     * @OA\Post(
     * path="api/update-password",
     * summary="update-password",
     * description="update-password",
     * operationId="update-password",
     * tags={"Forgot-password"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="user_id", type="integer", format="integer", example="3"),
     *          @OA\Property(property="password", type="password", format="password", example="1234"),
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


    public function updatePassword(Request $request)
    {
        $rules = array(
            'password' => 'min:6|max:254',
        );
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        }
        User::where('id', '=', $request->user_id)
            ->update([
                'password' => Hash::make($request->password)
            ]);
       RessetPassword::where([
            'user_id' => $request->user_id
        ])->delete();


            return response()->json([
                    'status' => true,
                    'message' => 'Ваш пароль успешно изменен!'
            ]);


//            return response()->json([
//                'status' => false,
//                'message' => 'Произошла ошибка!',

//        }
    }
}
