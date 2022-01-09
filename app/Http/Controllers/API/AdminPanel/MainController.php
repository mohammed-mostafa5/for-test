<?php

namespace App\Http\Controllers\API\AdminPanel;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Permission;

class MainController extends Controller
{

    public function getPermissions()
    {
        $permissions = Permission::get();
        return response()->json(compact('permissions'));
    }
}
