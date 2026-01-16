<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;
use App\Http\Resources\UserResource;

class AuthController extends Controller

{

    /**
     * Register user.
     *
     * @return json
     */
    /**
     * @OA\Post(
     * path="/auth/register",
     * summary="Register user",
     * description="Register user",
     * operationId="registerUser",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","password","phone","firstName","lastName"},
     *       @OA\Property(property="email", type="string",example="example@gmail.com"),
     *       @OA\Property(property="password", type="string", example="123456aA"),
     *       @OA\Property(property="phone", type="string", example="0902050807"),
     *       @OA\Property(property="firstName", type="string", example="User"),
     *       @OA\Property(property="lastName", type="string", example="Name"),
     * 
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Login Payload",
     *    ),
     * )
     */
    public function register(Request $request)
    {
        $rules = [
            'email' => 'email:rfc,dns',
            'password' => [
                'required',
                'string',
                'min:8',             // must be at least 8 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
            ],
            'phone' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response([
                "status" => 'error',
                "message" => $validation->errors()->all(),
            ], Response::HTTP_BAD_REQUEST);
        }

        $check = DB::table('users')->where('email', $request->email)->where('password', '!=', null)->first();
        if ($check) {
            return response([
                "status" => 'error',
                "message" => "Email already in use",
            ], Response::HTTP_BAD_REQUEST);
        }
        $user = User::create([
            "email" => $request->email,
            "password" => bcrypt($request->password),
            "phone" => $request->phone,
            "first_name" => $request->firstName,
            "last_name" => $request->lastName,
        ]);

        if (!empty($user)) {
            $data = [
                'email' => $request->email,
                'password' => $request->password
            ];
            return $this->login_return($data);
        }
    }

    /**
     * @OA\Post(
     * path="/auth/login",
     * summary="Login User",
     * description="Login user",
     * operationId="loginUser",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string",example="example@gmail.com"),
     *       @OA\Property(property="password", type="string", example="123456aA"),
     * 
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Login Payload",
     *    ),
     * )
     */


    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];

        return $this->login_return($data);
    }
    private function login_return($data)
    {
        $clients = DB::table('oauth_clients')->select('*')->where('provider', 'users')->orderByRaw('id DESC')->first();
        if (auth()->attempt($data)) {
            $path = env('APP_URL') . '/oauth/token';
            $response = Http::asForm()->post($path, [
                'grant_type' => 'password',
                'client_id' => $clients->id,
                'client_secret' => $clients->secret,
                'username' => $data['email'],
                'password' => $data['password'],
                'scope' => '',
            ]);

            $result['token'] = $response->json()['access_token'];
            $result['refresh_token'] = $response->json()['refresh_token'];

            $user = Auth::user();
            $user_data = new UserResource($user);

            return response()->json([
                'token' => $result['token'],
                'refreshToken' => $result['refresh_token'],
                'user' => $user_data,
            ], 200);
        } else {
            return response()->json([
                'massage' => 'Login fail',
                'details' => 'Incorrect email or password'
            ], 400);
        }
    }

    public function logout(Request $request)
    {
        return Auth::user()->id;
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }


    /**
     * @OA\Post(
     * path="/auth/refresh",
     * summary="Refresh user token",
     * description="Refresh user token",
     * operationId="refreshToken",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"refreshToken"},
     *       @OA\Property(property="refreshToken", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Auth Token",
     *    @OA\JsonContent(
     *       @OA\Property(property="refreshToken", type="string"),
     *        )
     *     )
     * )
     */

    public function refresh(Request $request)
    {
        $path = env('APP_URL') . '/oauth/token';
        $clients = DB::table('oauth_clients')->select('*')->where('provider', 'users')->orderByRaw('id DESC')->first();
        $response = Http::asForm()->post($path, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refreshToken,
            'client_id' => $clients->id,
            'client_secret' => $clients->secret,
            'scope' => '',
        ]);
        if (isset($response->json()['refresh_token'])) {
            return response()->json([
                'refresh_token' => $response->json()['refresh_token'],
            ]);
        } else {
            return response()->json([
                'massage' => 'Refresh fail',
                'details' => 'Incorrect refresh token'
            ], 400);
        }
    }

    /**
     * @OA\Post(
     * path="/auth/forgotpassword",
     * summary="Send mail forgot password",
     * description="Send mail forgot password",
     * operationId="SendmailForgotPass",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="",
     *    @OA\JsonContent(
     *       required={"email"},
     *       @OA\Property(property="email", type="string", format="email", example="example@gmail.com"),
     *    ),
     * ),
     * @OA\Response(
     *    response=404,
     *    description="Notfound",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Email not found")
     *        )
     *     )
     * )
     */
    public function forgotPasswordSendMail(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $data = [
                'my_password' => Str::lower(Str::random(4)) . Str::upper(Str::random(3)) . random_int(0, 9)
            ];
            $mail_details = [
                'email' => $request->email,
                'subject' => 'Forgot password',
            ];
            $send_result = Mail::send('forgotPasswordMail', $data, function ($message) use ($mail_details) {
                $message->to($mail_details['email']);
                $message->subject($mail_details['subject']);
            });
            if ($send_result == null) {
                $updatePasswordUser = $user->update([
                    'password' => bcrypt($data['my_password'])
                ]);
                return response()->json([
                    'response' => 1, 'message' => 'A mail has been sent to your mail id'
                ]);
            } else {
                return response()->json([
                    'response' => 2,
                    'message' => 'Unable to send mail'
                ]);
            }
        } else {
            return response()->json([
                'response' => 2,
                'message' => 'Email is not registered'
            ], Response::HTTP_NOT_FOUND);
        }
    }


    /**
     * @OA\Post(
     * path="/auth/changepassword",
     * summary="change password forgot",
     * description="change password forgot",
     * operationId="changePasswordForgot",
     * security={{"bearerAuth":{}}},
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="changePasswordForgot",
     *    @OA\JsonContent(
     *       required={"oldPassword","newPassword"},
     *       @OA\Property(property="oldPassword", type="string", format="password", example="123456aA"),
     *       @OA\Property(property="newPassword", type="string", format="password", example="123456bB"),
     *    ),
     * ),
     * @OA\Response(
     *    response=404,
     *    description="Notfound",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Email not found")
     *        )
     *     )
     * )    
     */
    public function changePassword(Request $request)
    {
        if (!(Hash::check($request->oldPassword, Auth::user()->password))) {
            return response()->json([
                'message' => 'Change password failed',
                'details' => 'Your current password does not matches with the password'
            ], 400);
        } else {
            $user = User::where('id', Auth::user()->id)->first();
            $updatePasswordUser = $user->update([
                'password' => bcrypt($request->newPassword)
            ]);
            if ($updatePasswordUser == 1) {
                return response()->json([
                    'message' => 'Update password success'
                ]);
            }
        }
    }

    public function updateTokenFirebase(Request $request)
    {
        try {
            $request->user()->update(['fcm_token' => $request->token]);
            return response()->json([
                'success' => true
            ]);
        } catch (\Exception $e) {
            report($e);
            return response()->json([
                'success' => false
            ], 500);
        }
    }
}
