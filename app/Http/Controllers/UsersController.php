<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    /**
     * Register a new user account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(UserCreateRequest $request)
    {
        $req = $request->all();
        $req['password'] = Hash::make($req['password']);

        $user = User::create($req);
        if ($user) {
            return response()->json([
                'message' => 'Successfully registered',
            ]);
        }
        return response()->json(['message' => 'Registration has failed'], 422);
    }
}
