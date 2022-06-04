<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserCreateRequest;
use App\Mail\UserConfirmationMail;
use App\Models\GroupInvite;
use App\Models\User;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;

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
            $group = Group::where('code', $request->input('group_code'))->first();
            if ($group) {
                $user->groups()->toggle($group->id);
            }
            
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

        $groups = $user->groups->map(function ($group) {
            return $group->code;
        });
        unset($user->groups);
        $user['groups'] = $groups;
        $user['token'] = $user->createToken($request->header('User-Agent'))->plainTextToken;
        $user['profile'] = $user->profile;

        return response()->json($user);
    }

    /**
     * User logout.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $cache = 'dashboards:home:'.$request->user()->id;
        Cache::forget($cache);
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
            'info' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $request->request->add(['email' => Crypt::decryptString($request->input('info'))]);

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
        $password = $user->password;

        if ($hasPassword) {
            if (!Hash::check($request->input('old_password'), $password)) {
                return response()->json([
                    'error' => trans('messages.general_error'),
                ], 401);
            }

            $password = Hash::make($request->input('password'));
        }

        $req = [
            'name' => $request->input('name') ?? $user->name,
            'password' => $password,
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
        $user->loadMissing('groups:code');
        return response()->json(['user' => $user]);
    }

    /**
     * List users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $users = User::has('groups')->get();
        $invites = GroupInvite::all()->merge($users);
        return response()->json($invites);
    }
}
