<?php

namespace App\Http\Controllers;

use App\Mail\SendMail;
use App\Models\Changenumber;
use App\Models\User;
use Illuminate\Http\Request;
use Validator;
use GreenSMS\GreenSMS;


class AddNumberController extends Controller
{
    /**
     * @OA\Post(
     * path="api/send-number",
     * summary="User Send Number for add",
     * description="User Send Number for add",
     * operationId="User Send Number for add",
     * tags={"Add Number"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Send Number for add",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="number", type="integer", format="number", example="1234567"),
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


    public function sendnumber(Request $request)
    {
        $rules = array(
            'number' => 'min:3|max:64|unique:users',
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        }

        if (isset(auth()->user()->number)) {
            return response()->json([
                'success' => false,
                'message' => 'you have number',
            ], 422);
        } else {

            $call_number = preg_replace('/[^0-9]/', '', $request->number);

            $randomNumber = random_int(100000, 999999);
            $credentails = [
                'number' => $call_number,
                'user_id' => auth()->user()->id,
                'random_int' => $randomNumber
            ];

            $CreateEmail = Changenumber::create($credentails);

            if ($CreateEmail) {
//                $client = new GreenSMS([
//                    'user' => 'sadn',
//                    'pass' => 'Dgdhh378qq',
//                ]);
//
//                $response = $client->sms->send([
//                    'to' => $call_number,
//                    'txt' => 'Here is your message for delivery' . $randomNumber
//                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'code successfully send in your number',
                    'varify' => $randomNumber
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
     * path="api/add-number",
     * summary="User Send code for add",
     * description="User Send code for add",
     * operationId="User Send Code for add",
     * tags={"Add Number"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Send code for add",
     *    @OA\JsonContent(
     *       required={"required"},
     *          @OA\Property(property="random_int", type="int", format="int", example="1234567"),
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


    public function addnumber(Request $request)
    {

        $code = $request->random_int;
        $user_id = $request->user_id;

        $user_number = Changenumber::where('random_int', $code)->get();
        if (!$user_number->isEmpty()) {
            $user = User::where('id', auth()->user()->id)->get();
            $number = User::where('id', auth()->user()->id)->update(['number' => $user_number[0]->number]);

            if ($number) {
                return response()->json([
                    'success' => true,
                    'message' => 'your number successfully added'
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
