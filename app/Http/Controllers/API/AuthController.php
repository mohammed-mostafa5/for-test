<?php

namespace App\Http\Controllers\API;

use App\Helpers\HelperTrait;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\EmailVerification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use HelperTrait;

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string', 'min:3', 'max:25'],
            'email'    => ['required','email', 'unique:users,email'],
            'phone'    => ['required','numeric'],
            'password' => ['required', 'confirmed','max:32', Password::defaults()],
        ])->validated();

        $validator['verification_code']  = $this->randomCode(6);
        $validator['code_expired_at']  = now()->addMinutes(10);

        $user = User::create($validator);

        Mail::to($user->email)->send(new EmailVerification($user));

        return response()->json([
            'message' => 'User registered Successfully, check your mail to get verification code.',
            'user'    => $user,
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'                => ['required','email', 'exists:users,email'],
            'verification_code'    => ['required','string'],
        ])->validated();

        $user = User::where($validator)->where('code_expired_at', '>', now() )->first();
        if (!$user) {
            throw ValidationException::withMessages(['verification_code' => 'Wrong verification code!']);
        }

        $user->update(['email_verified_at' => now()] );

        $token = $user->createToken('token')->accessToken;

        return response()->json([
            'message' => 'User registered Successfully',
            'user'    => $user,
            'token' => $token
        ]);
    }

    public function resendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'                => ['required','email', 'exists:users,email'],
        ])->validated();

        $user = User::where($validator)->first();
        $user->update([
            'verification_code'  => $this->randomCode(6),
            'code_expired_at'  => now()->addMinutes(10)
        ]);

        Mail::to($user->email)->send(new EmailVerification($user));

        return response()->json([
            'message' => 'Check your mail to get verification code.',
            'user'    => $user,
        ]);
    }

    public function login()
    {
        $credentials = request()->validate([
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|string|max:191'
        ]);

        if (!Auth::attempt($credentials) ) {
            throw ValidationException::withMessages(['password' => 'Wrong password!, Please try again']);
        }

        $token = request()->user()->createToken('token')->accessToken;

        return response()->json(['token' => $token]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json(['message' => 'You logged out Successfully']);
    }
}
