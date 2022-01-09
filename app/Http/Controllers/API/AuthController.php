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

    // Customer registration
    public function register(Request $request)
    {
        // validate data
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string', 'min:3', 'max:25'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['required', 'numeric'],
            'password' => ['required', 'confirmed', 'max:32', Password::defaults()],
        ])->validated();

        $validator['verification_code']  = $this->randomCode(6);
        $validator['code_expired_at']  = now()->addMinutes(10);

        // store user data
        $user = User::create($validator);

        // send verification mail
        Mail::to($user->email)->send(new EmailVerification($user));

        return response()->json([
            'message' => 'User registered Successfully, check your mail to get verification code.',
            'user'    => $user,
        ]);
    }

    public function verifyEmail(Request $request)
    {
        // validate data
        $validator = Validator::make($request->all(), [
            'email'                => ['required', 'email', 'exists:users,email'],
            'verification_code'    => ['required', 'string'],
        ])->validated();

        // define user and check is code still valid
        $user = User::where($validator)->where('code_expired_at', '>', now())->first();

        // check validation result
        if (!$user) {
            throw ValidationException::withMessages(['verification_code' => 'Wrong verification code!']);
        }

        // verify email
        $user->update(['email_verified_at' => now()]);

        // generate client personal token
        $token = $user->createToken('token')->accessToken;

        return response()->json([
            'message' => 'Email verified Successfully',
            'user'    => $user,
            'token' => $token
        ]);
    }

    public function resendCode(Request $request)
    {
        // validate data
        $validator = Validator::make($request->all(), [
            'email'                => ['required', 'email', 'exists:users,email'],
        ])->validated();

        // define user
        $user = User::where($validator)->first();

        // renew user code
        $user->update([
            'verification_code'  => $this->randomCode(6),
            'code_expired_at'  => now()->addMinutes(10)
        ]);

        // send mail with new verification code
        Mail::to($user->email)->send(new EmailVerification($user));

        return response()->json([
            'message' => 'Check your mail to get verification code.',
            'user'    => $user,
        ]);
    }

    public function login()
    {
        // validate data
        $credentials = request()->validate([
            'email'    => 'required|email|exists:users,email',
            'password' => 'required|string|max:191'
        ]);
        // check credentials
        if (!Auth::attempt($credentials)) {
            throw ValidationException::withMessages(['password' => 'Wrong password!, Please try again']);
        }

        // define user
        $user = User::where('email', request('email'))->first();

        // check if user have an active email
        if (!$user->email_verified_at) {
            throw ValidationException::withMessages(['email' => 'Your email not active, please activate it and try again!']);
        }

        // generate token
        $token = request()->user()->createToken('token')->accessToken;

        return response()->json(['token' => $token]);
    }

    // Forgot password process
    public function forgotPassword(Request $request)
    {
        // validate data
        $validator = Validator::make($request->all(), [
            'email'       => ['required', 'email', 'exists:users,email'],
        ])->validated();
        // define user
        $admin = User::where('email', $request->email)->first();
        // renew verification code
        $admin->update([
            'verification_code' => $this->randomCode(6),
            'code_expired_at'   => now()->addMinutes(10),
        ]);
        // send mail with new verification code
        Mail::to($admin->email)->send(new EmailVerification($admin));

        return response()->json([
            'message' => 'Check your mail to get verification code.',
        ]);
    }

    // Resetting password after verify email
    public function resetPassword(Request $request)
    {
        // validate data
        $validator = Validator::make($request->all(), [
            'email'                => ['required', 'email', 'exists:users,email'],
            'verification_code'    => ['required', 'string'],
            'password'             => ['required', 'confirmed', 'max:32', Password::defaults()],
        ])->validated();
        // define user and check is code still valid
        $user = User::where('email', $request->email)->where('code_expired_at', '>', now())->first();
        // code validation result
        if (!$user) {
            throw ValidationException::withMessages(['verification_code' => 'Wrong verification code!']);
        }
        // verify email
        $user->update(['email_verified_at' => now()]);

        // generate token
        $token = $user->createToken('token')->accessToken;

        return response()->json([
            'message' => 'Password Updated Successfully',
            'user'    => $user,
            'token' => $token
        ]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json(['message' => 'You logged out Successfully']);
    }
}
