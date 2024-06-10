<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendResetMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class PasswordResetController extends Controller
{


    public function generateToken(Request $request, $email)
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Generate the token
        $token = Str::random(60);

        // Insert the token into the password_resets table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => $token,
                'created_at' => now(),
            ]
        );

        return $token;
    }

    /**
     * Send a reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $data = [
                "status" => 400,
                "message" => $validator->messages()
            ];

            return response()->json($data, 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $token = $this->generateToken($request, $request->email);

        $details = [
            'token' => $token,
            "email" => $request->email,
            "first_name" => $user->first_name,
        ];

        Mail::to($details['email'])->send(new SendResetMail($details));
        // print_r("hi");
        return response()->json([
            'message' => 'Reset link sent successfully'
        ], 200);
    }

    /**
     * Handle a reset password request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function reset(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required|min:8',
        ]);

        if ($validator->fails()) {
            $data = [
                "status" => 400,
                "message" => $validator->messages()
            ];

            return response()->json($data, 400);
        }

        $token = DB::table('password_reset_tokens')->where('token', $request->token)->first();

        if (!$token) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        $user = User::where("email", $token->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $token->email)->delete();

        return response()->json(['message' => 'Password reset successfully'], 200);

    }

    public function checkToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            $data = [
                "status" => 400,
                "message" => $validator->messages()
            ];

            return response()->json($data, 400);
        }

        $token = DB::table('password_reset_tokens')->where('token', $request->token)->first();

        if (!$token) {
            return response()->json(['message' => 'Invalid token'], 400);
        }

        return response()->json(['message' => 'Token is valid'], 200);
    }


}
