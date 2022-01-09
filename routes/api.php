<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\AdminPanel\AdminsController;
use App\Http\Controllers\API\AdminPanel\CustomersController;
use App\Http\Controllers\API\AdminPanel\MainController;
use App\Http\Controllers\API\AdminPanel\RolesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


/////////////////////////////// Start Auth //////////////////////////////////
Route::post('user/register', [ AuthController::class,'register']);
Route::post('user/login', [ AuthController::class,'login']);
Route::post('user/email-verification', [ AuthController::class,'verifyEmail']);
Route::post('user/resend-code', [ AuthController::class,'resendCode']);
Route::post('user/forgot-password', [ AuthController::class,'forgotPassword']);
Route::post('user/reset-password', [ AuthController::class,'resetPassword']);
//////////////////////////////// End Auth //////////////////////////////////


Route::prefix('customer')->middleware('auth:api')->group(function () {
    Route::post('logout', [ AuthController::class,'logout']);

});

Route::prefix('admin')->middleware(['auth:api', 'admin', 'permission.handler'])->group(function () {

    Route::apiResource('admins', AdminsController::class );
    Route::apiResource('customers', CustomersController::class );
    Route::apiResource('roles', RolesController::class );

    Route::get('roles', [ MainController::class, 'getRoles'])->name('roles.index');
    Route::get('permissions', [ MainController::class, 'getPermissions'])->name('permissions.index');
    Route::get('profile', [ MainController::class, 'profile'])->name('profile.show');
    Route::post('profile/update', [ MainController::class, 'updateProfile'])->name('profile.update');
    Route::post('update-password', [ MainController::class, 'updatePassword' ])->name('password.update');
    Route::post('update-email', [ MainController::class, 'updateEmail' ])->name('email.update');

});

