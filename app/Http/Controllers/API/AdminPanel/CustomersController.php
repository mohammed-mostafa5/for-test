<?php

namespace App\Http\Controllers\API\AdminPanel;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CustomersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // get filtered customers
        $customers = User::where('is_admin', false)
                ->when($request->email, function ($customers) use ($request){
                    $customers->where('email', 'like', '%' . $request->email . '%');
                })
                ->when($request->name, function ($customers) use ($request){
                    $customers->where('name', 'like', '%' . $request->name . '%');
                })->get();

        return response()->json(compact('customers'));
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
            'phone'    => ['required','numeric'],
        ])->validated();

        $customer = User::create($validator);

        return response()->json(compact('customer'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customer = User::findOrFail($id);
        return response()->json(compact('customer'));
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
            'phone'    => ['required','numeric'],
        ])->validated();

        $customer = User::findOrFail($id);
        $customer->update($validator);

        return response()->json(compact('customer'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $customer = User::findOrFail($id);
        $customer->delete();

        return response()->json(['message' => 'customer deleted successfully.']);
    }
}
