<?php

namespace App\Http\Controllers\API\AdminPanel;

use App\Models\User;
use App\Helpers\HelperTrait;
use Illuminate\Http\Request;
use App\Mail\EmailVerification;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class MainController extends Controller
{
    use HelperTrait;

    ################################### Helpers ###################################

    public function getPermissions()
    {
        $permissions = Permission::get();
        return response()->json(compact('permissions'));
    }

    public function getRoles()
    {
        $roles = Role::get();
        return response()->json(compact('roles'));
    }



    ################################### Account ###################################

    public function profile()
    {
        $admin = auth('api')->user();
        return response()->json(compact('admin'));
    }

    public function updateProfile(Request $request)
    {
        // validate data
        $validator = Validator::make($request->all(), [
            'name'     => ['sometimes', 'string', 'min:3', 'max:25'],
            'avatar'   => ['sometimes', 'image', 'mimes:jpg,png,jpeg'],
        ])->validated();

        $admin = auth('api')->user();
        $admin->update($validator);

        return response()->json([
            'message' => 'Profile Updated Successfully',
            'admin'   => $admin
        ]);
    }

    public function updatePassword(Request $request)
    {
        // validate data
        $validator = Validator::make($request->all(), [
            'old_password'     => ['required', 'string', 'min:3', 'max:191'],
            'password'         => ['required', 'confirmed', 'max:32', Password::defaults()],
        ])->validated();

        // check old password process
        $admin = auth('api')->user();
        if (Hash::check(request('old_password'), $admin->password)) {
            $admin->update(['password' => $request->password]);
            return response()->json([
                'message' => 'Password Updated Successfully'
            ]);
        }

        return response()->json([
            'message' => 'Wrong Old Password'
        ], 403);
    }

    public function updateEmail()
    {
        // validate data
        request()->validate([
            'password'     => 'required|string|max:191',
            'email'        => "required|string|email|unique:users,email," . auth('api')->id(),
        ]);

        // check password & update email then reset user mail to be inactive
        $admin = auth('api')->user();
        if (Hash::check(request('password'), $admin->password)) {
            $admin->update([
                'email'             => request('email'),
                'verification_code' => $this->randomCode(6),
                'code_expired_at'   => now()->addMinutes(10),
                'email_verified_at' => null,
            ]);

            // send verification mail
            Mail::to($admin->email)->send(new EmailVerification($admin));

            return response()->json([
                'message' => 'Email Updated Successfully'
            ]);
        }

        // return authorization error if password is incorrect
        return response()->json([
            'message' => 'Wrong Password'
        ], 403);
    }



}
