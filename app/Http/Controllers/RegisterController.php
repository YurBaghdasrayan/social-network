<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Mail\SendMail;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use GreenSMS\GreenSMS;
use Illuminate\Support\Facades\Mail;
use Exception;
use Validator;


class RegisterController extends Controller
{
    /**
     * @OA\Post(
     * path="api/registration",
     * summary="User Register",
     * description="User Register here",
     * operationId="Register",
     *
     *
     *
     *
     * tags={"Register"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Register here",
     *    @OA\JsonContent(
     *               required={"name","email", "password", "password_confirmation","surname","patronymic","city","username","date_of_birth","number"},
     *               @OA\Property(property="name", type="text"),
     *               @OA\Property(property="email", type="email"),
     *               @OA\Property(property="password", type="password"),
     *               @OA\Property(property="password_confirmation", type="password"),
     *               @OA\Property(property="surname", type="text"),
     *               @OA\Property(property="patronymic", type="text"),
     *               @OA\Property(property="city", type="text"),
     *               @OA\Property(property="username", type="text"),
     *               @OA\Property(property="date_of_birth", type="datetime"),
     *               @OA\Property(property="number", type="integer"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="User Register here",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */

    public function store(RegisterRequest $request)
    {
//        $rules = array(
//            'name' => 'required|min:3|max:64',
//            'surname' => 'required|min:3|max:64',
//            'password' => 'required|min:6|max:64|confirmed',
//            'password_confirmation' => 'required|min:6|max:64',
//            'patronymic' => 'required|min:3|max:64',
//            'city' => 'required',
////            'username' => 'unique:users|required',
//            'date_of_birth' => 'required',
//        );
//        $validator = Validator::make($request->all(), $rules);

        ///// chbacel koment@
//        if ($validator->fails()) {
//            return $validator->errors();
//        }


//        $data = $request->validated();
        $randomNumber = random_int(100000, 999999);
        if ($request->email) {
            $rules = array(
                'email' => 'required|min:3|max:64|unique:users|email',
                'name' => 'required|min:3|max:64',
                'surname' => 'required|min:3|max:64',
                'password' => 'required|min:6|max:64|confirmed',
                'password_confirmation' => 'required|min:6|max:64',
                'patronymic' => 'required|min:3|max:64',
                'city' => 'required',
                'username' => 'unique:users|required',
                'date_of_birth' => 'required',
            );
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $validator->errors();
            }

            $dateStr = $request->date_of_birth;
            $dateArray = date_parse_from_format('Y-m-d', $dateStr);

            $user = User::create([
                'name' => $request->name,
                'surname' => $request->surname,
                'email' => $request->email,
                'role_id' => Role::USER_ID,
                'verify_code' => $randomNumber,
                'password' => Hash::make($request->password),
                'patronymic' => $request->patronymic,
                'city' => $request->city,
                'username' => $request->username,
                'date_of_birth' => $dateStr,
                'day' => $dateArray['day'],
                'mount' => $dateArray['month'],
            ]);
            if ($user) {
                $details = [
                    'email' => $user->name,
                    'verification_at' => $randomNumber,
                ];
            }
            Mail::to($user->email)->send(new SendMail($details));
            return response()->json([
                'success' => true,
                'message' => 'Register Successfully',
                'verify code' => $randomNumber
            ], 200);
        } else {
            $rules = array(
                'number' => 'required|min:3|max:64|unique:users',
                'name' => 'required|min:3|max:64',
                'surname' => 'required|min:3|max:64',
                'password' => 'required|min:6|max:64|confirmed',
                'password_confirmation' => 'required|min:6|max:64',
                'patronymic' => 'required|min:3|max:64',
                'city' => 'required',
                'username' => 'unique:users|required',
                'date_of_birth' => 'required',
            );

            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return $validator->errors();
            }

            $dateStr = $request->date_of_birth;
            $dateArray = date_parse_from_format('Y-m-d', $dateStr);

            $number = $request->number;

            $call_number = preg_replace('/[^0-9]/', '', $number);
            $user = User::create([
                'name' => $request->name,
                'role_id' => Role::USER_ID,
                'surname' => $request->surname,
                'number' => $call_number,
                'verify_code' => $randomNumber,
                'password' => Hash::make($request->password),
                'patronymic' => $request->patronymic,
                'city' => $request->city,
                'username' => $request->username,
                'date_of_birth' => $dateStr,
                'day' => $dateArray['day'],
                'mount' => $dateArray['month'],
            ]);

            try {
                $client = new GreenSMS([
                    'user' => 'sadn',
                    'pass' => 'Dgdhh378qq',
                ]);

                $response = $client->sms->send([
                    'from' => 'Vatan',
                    'to' => $call_number,
                    'txt' => 'Ваш код потверждения' . ' ' . $randomNumber
                ]);
            } catch (Exception $e) {
                User::where('id', $user->id)->delete();
                return response()->json([
                    'status' => false,
                    'message' => 'Error in Green Smms',
                ]);
            }


            if ($user) {
                return response()->json([
                    'success' => true,
                    'message' => 'user successfully registered',
                    'verify code' => $randomNumber
                ], 200);
            }
        }
    }


    public function SendCodeTwo(Request $request)
    {
        $randomNumber = random_int(100000, 999999);
        if ($request->number != null) {
            $number = $request->number;
            $call_number = preg_replace('/[^0-9]/', '', $number);
            try {
                $client = new GreenSMS([
                    'user' => 'sadn',
                    'pass' => 'Dgdhh378qq',
                ]);
                $response = $client->sms->send([
                    'to' => $call_number,
                    'txt' => 'Ваш код потверждения' . $randomNumber
                ]);
                User::where('number', $request->number)->where('verify_code', '!=', 1)->update([
                    'verify_code' => $randomNumber
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'Error in Green Smms',
                ]);
            }
            return response()->json([
                'status' => true,
                'message' => 'code send your number',
                'code' => $randomNumber,
            ], 200);
        }
        if ($request->email != null) {
            User::where('email', $request->email)->where('verify_code', '!=', 1)->update([
                'verify_code' => $randomNumber
            ]);
            $details = [
                'email' => $request->email,
                'verification_at' => $randomNumber,
            ];
            Mail::to($request->email)->send(new SendMail($details));
            return response()->json([
                'status' => true,
                'message' => 'code send your email',
                'code' => $randomNumber,
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Kisat prat tvyalner Mi uxarki Mane Jan'
            ], 422);
        }
    }
}
