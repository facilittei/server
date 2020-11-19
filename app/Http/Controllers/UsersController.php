<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Mail\UserConfirmationMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

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
            Mail::to($user->email)->queue(new UserConfirmationMail($user));
            return response()->json([
                'message' => trans('messages.register_success'),
            ]);
        }

        return response()->json(['error' => trans('messages.register_failed')], 422);
    }

    /**
     * User login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $req = $request->all();
        $user = User::where('email', $req['email'])->first();

        if (!$user || !Hash::check($req['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [trans('passwords.user')],
            ]);
        }

        return response()->json([
            'token' => $user->createToken($request->header('User-Agent'))->plainTextToken,
        ]);
    }

    /**
     * User logout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
    }

    /**
     * User email verification.
     *
     * @return \Illuminate\Http\Response
     */
    public function verify($hash)
    {
        list($id, $created_at) = explode('-', $hash);
        $user = User::where('id', $id)->where('created_at', base64_decode($created_at))->first();

        if ($user) {
            $user->update(['email_verified_at' => Carbon::now()]);
            return response()->json(['user' => $user]);
        }

        return response()->json([
            'error' => trans('auth.invalid_verification_token')
        ], 401);
    }
}
