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
use Illuminate\Support\Facades\Http;
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
     * @OA\RequestBody(
     *    required=true,
     *    description="Pass user credentials",
     *    @OA\JsonContent(
     *       required={"email","password","phone","firstname","lastname"},
     *       @OA\Property(property="email", type="string",example="example@gmail.com"),
     *       @OA\Property(property="password", type="string", example="123456"),
     *       @OA\Property(property="phone", type="string", example="0902050807"),
     *       @OA\Property(property="firstname", type="string", example="User"),
     *       @OA\Property(property="lastname", type="string", example="Name"),
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
            $result['user'] = [
                'fullname' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                'email' => Auth::user()->email,
                'phone' => Auth::user()->phone,
            ];
            return response()->json([
                'token' => $result['token'],
                'refresh_token' => $result['refresh_token'],
                'user' => $result['user'],
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
     * summary="Logout",
     * description="logout",
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
        return Auth::user()->id;
        $path = env('APP_URL') . '/oauth/token';
        $response = Http::asForm()->post($path, [
            'grant_type' => 'refresh_token',
            'refresh_token' => $request->refreshToken,
            'client_id' => '2',
            'client_secret' => 'POU6G4TMVzoFqxNMOqRxJ8Wv8CgmrATLUeUNtGpL',
            'scope' => '',
        ]);

        return $response->json();
        return response()->json([
            'access_token' => $response->json()['access_token'],
            'refresh_token' => $response->json()['refresh_token'],
        ]);
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
                'my_password' => Str::random(8),
            ];
            $mail_details = [
                'email' => $request->email,
                'subject' => 'Forgot password',
            ];
            $send_result = Mail::send('forgotPasswordMail', $data, function ($message) use ($mail_details) {
                $message->to($mail_details['email']);
                $message->subject($mail_details['subject']);
            });
            // dd($send_result);
            if ($send_result == null) {
                $updatePasswordUser = $user->update([
                    'password' => bcrypt($data['my_password'])
                ]);
                return response()->json([
                    'message' => 'We have sent your new password by email'
                ]);
            } else {
                return response()->json([
                    'message' => 'Send mail fail'
                ]);
            }
        } else {
            return response()->json([
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
     *       @OA\Property(property="oldPassword", type="string", format="password", example="123456"),
     *       @OA\Property(property="newPassword", type="string", format="password", example="123456pix"),
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
                'message' => 'Your current password does not matches with the password'
            ]);
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
