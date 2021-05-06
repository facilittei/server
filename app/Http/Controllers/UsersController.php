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
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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
                'email' => [trans('auth.failed')],
            ]);
        }

        if (!$user->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => [trans('auth.email_not_confirmed')],
            ]);
        }

        return response()->json([
            'token' => $user->createToken($request->header('User-Agent'))->plainTextToken,
            'user' => $user,
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

    /**
     * User password recovery.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function recover(Request $request)
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => trans('passwords.sent')]);
        }

        return response()->json([
            'error' => trans('messages.general_error')
        ], 401);
    }

    /**
     * User password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();

                $user->setRememberToken(Str::random(60));

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => trans('passwords.reset')]);
        }

        return response()->json([
            'error' => trans('messages.general_error')
        ], 401);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = $request->user();
        $hasPassword = $request->input('password') && strlen($request->input('password')) >= 8;

        $req = [
            'name' => $request->input('name') ?? $user->name,
            'password' => $hasPassword ? Hash::make($request->input('password')) : $user->password,
        ];

        if ($request->user()->update($req)) {
            return response()->json(['message' => trans('messages.general_update')]);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 401);
    }

    /**
     * Get user info.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json(['user' => $user]);
    }
}
