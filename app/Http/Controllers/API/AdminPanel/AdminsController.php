<?php

namespace App\Http\Controllers\API\AdminPanel;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class AdminsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // get filtered admins
        $admins = User::where('is_admin', true)
                ->when($request->email, function ($admins) use ($request){
                    $admins->where('email', 'like', '%' . $request->email . '%');
                })
                ->when($request->name, function ($admins) use ($request){
                    $admins->where('name', 'like', '%' . $request->name . '%');
                })->get();

        return response()->json(compact('admins'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => ['required', 'string', 'min:3', 'max:25'],
            'email'    => ['required','email', 'unique:users,email'],
            'password' => ['required', 'confirmed','max:32', Password::defaults()],
            'avatar'   => ['required', 'image', 'mimes:jpg,png,jpeg'],
            'role_id'  => ['nullable', 'exists:roles,id'],
        ])->validated();

        $validator['is_admin'] = true;

        $admin = User::create($validator);
        $admin->syncRoles([$request->role_id]);

        return response()->json(compact('admin'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $admin = User::findOrFail($id);
        return response()->json(compact('admin'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'     => ['sometimes', 'string', 'min:3', 'max:25'],
            'email'    => ['sometimes','email', "unique:users,email," . $id],
            'password' => ['sometimes', 'confirmed','max:32', Password::defaults()],
            'avatar'   => ['sometimes', 'image', 'mimes:jpg,png,jpeg'],
            'role_id'  => ['nullable', 'exists:roles,id'],
        ])->validated();

        $admin = User::findOrFail($id);
        $admin->update($validator);
        $admin->syncRoles([$request->role_id]);


        return response()->json(compact('admin'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $admin = User::findOrFail($id);
        $admin->delete();

        return response()->json(['message' => 'Admin deleted successfully.']);
    }
}
