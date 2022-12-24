<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GroupsController extends Controller
{
    /**
     * Get groups.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function list()
    {
        $groups = Group::all();

        return response()->json($groups);
    }

    /**
     * Create a new group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $name = $request->input('name');
        $existing_group = Group::where('name', $name)->first();

        if ($existing_group) {
            return response()->json(['error' => trans('messages.register_failed')], 404);
        }

        $slug = Str::slug($name, '_');
        $request['code'] = $slug;

        $group = Group::create($request->all());
        if ($group) {
            return response()->json([
                'message' => trans('messages.register_success'),
            ]);
        }

        return response()->json(['error' => trans('messages.register_failed')], 422);
    }

    /**
     * Delete group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $group_id)
    {
        $group = Group::findOrFail($group_id);
        if ($group->delete()) {
            return response()->json([
                'message' => trans('messages.general_destroy'),
            ]);
        }

        return response()->json(['error' => trans('messages.register_failed')], 422);
    }

    /**
     * Remove user from group.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function annul(Request $request, $group_id, $user_id)
    {
        $group = Group::findOrFail($group_id);
        $user = User::findOrFail($user_id);
        $user->groups()->toggle($group->id);

        return response()->json([
            'message' => trans('messages.register_success'),
        ]);
    }
}
