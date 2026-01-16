<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\PasswordReset;
use Lcobucci\JWT\Parser as JwtParser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Str;

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
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","password","phone","firstname","lastname"},
     *       @OA\Property(property="email", type="string",example="bao@gmail.com"),
     *       @OA\Property(property="password", type="string", example="123456"),
     *       @OA\Property(property="phone", type="string", example="0902050807"),
     *       @OA\Property(property="firstname", type="string", example="admin"),
     *       @OA\Property(property="lastname", type="string", example="admin"),
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
        $this->validate($request, [
            'password' => 'required|min:6',
            function ($attribute, $value, $fail) {
                if (User::whereEmail($value)->count() > 0) {
                    $fail($attribute . ' is already used.');
                }
            },
        ]);
        $user = User::create([
            "email" => $request->email,
            "password" => bcrypt($request->password),
            "phone" => $request->phone,
            "firstname" => $request->firstname,
            "lastname" => $request->lastname,
        ]);
        if ($user) {
            return response()->json([
                'user' => $user,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Register fail',
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Login user.
     *
     * @return json
     */
    /**
     * @OA\Post(
     * path="/auth/login",
     * summary="Login User",
     * description="Login user",
     * operationId="loginUser",
     * tags={"Authentication"},
     * security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string",example="bao@gmail.com"),
     *       @OA\Property(property="password", type="string", example="123456"),
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

        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
            $user = [
                'email' => Auth::user()->email,
                'phone' => Auth::user()->phone,
                'firstname' => Auth::user()->firstname,
                'lastname' => Auth::user()->lastname
            ];
            $oauth_access_tokens = DB::table('oauth_access_tokens')
                ->where('user_id', Auth::user()->id)
                ->select('expires_at')
                ->orderByRaw('id DESC')->first();
            $expires_at = $oauth_access_tokens->expires_at;
            $expires_at = \Carbon\Carbon::parse($expires_at)->timestamp;
            $exp_at_timestamp_mili = $expires_at * 1000;
            return response()->json([
                'token' => $token,
                'user' => $user,
                'expires_at' => $exp_at_timestamp_mili,
            ], 200);
        } else {
            return response()->json([
                'status' => 'Login fail',
                'message' => 'Incorrect email or password'
            ], 401);
        }
    }

    /**
     * @OA\Get(
     * path="/auth/logout",
     * tags={"Authentication"},
     * summary="Logout User",
     * description="Logout User",
     * security={{"bearerAuth":{}}},
     * operationId="logout",
     * @OA\Response(
     *    response=401,
     *    description="Wrong credentials response",
     *    @OA\JsonContent(
     *       @OA\Property(property="message", type="string", example="Unauthenticated")
     *        )
     *     )
     * )
     */

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Refresh Token.
     *
     * @return json
     */

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
        $token = $request->token;
        $token_id = app(JwtParser::class)->parse($token)->claims()->get('jti');
        $oauth_access_tokens = DB::table('oauth_access_tokens')->where('id', $token_id)->first();

        $date = date('Y-m-d H:i:s');
        $today_stamp = strtotime($date);
        $expires = $oauth_access_tokens->expires_at;
        $expires_stamp = strtotime($expires);
        $revoked = $oauth_access_tokens->revoked;
        $exp = $expires_stamp - $today_stamp;
        if ($revoked != 0 || $exp <= 0) {
            return false;
        } else {
            $user_id = $oauth_access_tokens->user_id;
            $user = User::whereid($user_id)->first();

            return response()->json([
                'email' => $user->email,
            ], 200);
        }
    }

    /**
     * Forgot Password.
     *
     * @return json
     */

    /**
     * @OA\Post(
     * path="/auth/fortgotpassword",
     * summary="Forgot Password",
     * description="Forgot Password",
     * operationId="forgotPassword",
     * tags={"Authentication"},
     * @OA\Parameter(
     *    name="username",
     *    @OA\Schema(
     *      type="string",
     *    ),
     *    in="query",
     *    required=true,
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    )
     * )
     */

    public function forgotPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $passwordReset = PasswordReset::updateOrCreate([
                'email' => $user->email,
            ], [
                'token' => Str::random(60),
            ]);
            $data = [
                'token' => $passwordReset['token'],
                'router' => env('RESET_PASSWORD_LINK'),
            ];
            $mail_details = [
                'email' => $passwordReset['email'],
                'subject' => 'Forgot password',
            ];
            Mail::send('forgotPasswordMail', $data, function ($message) use ($mail_details) {
                $message->to($mail_details['email']);
                $message->subject($mail_details['subject']);
            });

            return response()->json([
                'message' => 'We have e-mailed your password reset link!'
            ]);
        } else {
            return response()->json([
                'message' => 'Email is not registered'
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Change Password.
     *
     * @return json
     */

    /**
     * @OA\Post(
     * path="/auth/changepassword",
     * summary="Change Password",
     * description="Change Password",
     * operationId="changePassword",
     * tags={"Authentication"},
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"oldPassword", "newPassword"},
     *       @OA\Property(property="oldPassword", type="string"),
     *       @OA\Property(property="newPassword", type="string"),
     *    ),
     * ),
     * @OA\Response(
     *    response=200,
     *    description="Success",
     *    )
     * )
     */

    public function changePassword(Request $request, $token)
    {
        $passwordReset = PasswordReset::where('token', $token)->firstOrFail();
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(4320)->isPast()) {
            return response()->json([
                'message' => 'This password reset token is invalid.',
            ], 422);
        }
        $user = User::where('email', $passwordReset->email)->firstOrFail();
        $password = bcrypt($request->password);
        $updatePasswordUser = $user->update(['password' => $password]);
        $passwordReset->delete();

        return response()->json([
            'success' => $updatePasswordUser,
        ]);
    }
}
