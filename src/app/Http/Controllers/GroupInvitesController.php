<?php

namespace App\Http\Controllers;

use App\Mail\GroupInviteMail;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupInvite;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class GroupInvitesController extends Controller
{
    /**
     * Invite user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function invite(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'groupId' => 'required'
        ]);
        $group = Group::findOrFail($request->input('groupId'));
        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            $invite = GroupInvite::firstOrCreate([
                'group_id' => $group->id,
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'token' => (new GroupInvite)->generateToken($group->id),
            ]);
            Mail::to($invite->email)->queue(new GroupInviteMail($group, $invite));
            return response()->json(['message' => trans('messages.general_success')]);
        }

        return response()->json(['error' => trans('messages.register_failed')], 422);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request, $token)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $identify = (new GroupInvite)->identifyToken($token);
        $group_id = $identify['group_id'];

        if (isset($identify['error'])) {
            return response()->json([
                'error' => trans('auth.unauthorized'),
            ], 401);
        }

        $groupInvite = GroupInvite::where('group_id', $group_id)
            ->where('token', $token)
            ->first();

        if (!$groupInvite) {
            return response()->json([
                'error' => trans('auth.invalid_verification_token'),
            ], 401);
        }

        $req = $request->all();
        $user = User::create([
            'name' => $groupInvite->name,
            'email' => $groupInvite->email,
            'password' => bcrypt($req['password']),
            'email_verified_at' => Carbon::now(),
        ]);

        if ($user) {
            $user->groups()->toggle($group_id);
            $groups = $user->groups->map(function ($group) {
                return $group->code;
            });
            unset($user->groups);
            $user['groups'] = $groups;
            $groupInvite->delete();

            $user['groups'] = $groups;
            $user['token'] = $user->createToken($request->header('User-Agent'))->plainTextToken;
            return response()->json($user);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 422);
    }

    /**
     * Display a listing of the resource (invites).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function invites(Request $request)
    {
        return response()->json(GroupInvite::all());
    }
}
