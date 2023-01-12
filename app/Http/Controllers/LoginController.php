<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class LoginController extends Controller
{
    /**
     * @OA\Post(
     * path="api/login",
     * summary="User Login",
     * description="User Login here",
     * operationId="Login",
     * tags={"Login"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Login here",
     *    @OA\JsonContent(
     *               required={"email", "password","number"},
     *               @OA\Property(property="email", type="email"),
     *               @OA\Property(property="password", type="password"),
     *               @OA\Property(property="number", type="integer"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="User Login here",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */

    public function store(Request $request)
    {
        $rules = array(
            'email' => 'max:255',
            'number' => 'max:255',
            'password' => 'required|min:6'
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        }


        if ($request->email && $request->email!= null) {

            $getUser = User::where('email', $request->email)->first();

            if($getUser != null){
                if($getUser->verify_code != 1){
                    return response()->json([
                       'status' => false,
                       'message' => 'no veryfi user'
                    ], 422);
                }
            }

            if (Auth::attempt($request->only('email','password'))) {
                $token = auth()->user()->createToken('API Token')->accessToken;
                User::where('id', auth()->user()->id)->update(['last_seen' => 'online']);
                return response(['user' => auth()->user(), 'token' => $token], 200);
            } else {
                return response(['error_message' => 'Incorrect Details. Please try again']);
            }
        } else {
            $getUser = User::where('number', $request->number)->first();
            if($getUser != null){
                if($getUser->verify_code != 1){
                    return response()->json([
                        'status' => false,
                        'message' => 'no veryfi user'
                    ], 422);
                }
            }
                 $call_number = preg_replace('/[^0-9]/', '', $request->number);
            $request['number'] = $call_number;
            $loginrt = Auth::attempt($request->only('number','password'));

            if ($loginrt == true ) {
                $token = auth()->user()->createToken('API Token')->accessToken;
                return response(['user' => auth()->user(), 'token' => $token], 200);
            } else {

                return response(['error_message' => 'Incorrect Details. Please try again']);
            }
        }
    }

    /**
     * @OA\Post(
     * path="api/verify",
     * summary="User Verify",
     * description="User Verify here",
     * operationId="Verify",
     * tags={"Verify"},
     * @OA\RequestBody(
     *    required=true,
     *    description="User Verify here",
     *    @OA\JsonContent(
     *               required={"verified_code"},
     *               @OA\Property(property="verified_code", type="int"),
     *               @OA\Property(property="number", type="int"),
     *               @OA\Property(property="email", type="int"),
     *
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="User Verify here",
     *    @OA\JsonContent(
     *        )
     *     )
     * )
     */

    public function verify(Request $request)
    {

        $rules = array(
            'verified_code' => 'min:6|max:254',
        );

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        }
        $user_code = $request->verified_code;

        if(isset($request->number)){
            $call_number = preg_replace('/[^0-9]/', '', $request->number);
            $users = User::where('verify_code', '=', $user_code)->where('number', $call_number)->first();
        }elseif($request->email){
            $users = User::where('verify_code', '=', $user_code)->where('email', $request->email)->first();
        }else{
            return response()->json([
               'status' => false,
               'message' =>  'Mane jan es kisat prat tvyalneri het es inch anem ))'
            ],422);
        }
        if ($users != null) {
            $user_id = $users->id;
            $token =   $users->createToken('API Token')->accessToken;
            $updating = User::where('id', '=', $user_id)->update(['verify_code' => 1]);
            Auth::login($users);
                return response()->json([
                    'success' => true,
                    'message' => 'user successfully verified',
                    'user' =>$users,
                    'token' => $token,

                ], 200);
    }else{
            return response()->json([
                'success' => false,
                'message' => 'Cod@ sxala Mane jan'
            ], 422);
        }

    }
}
