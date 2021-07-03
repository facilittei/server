<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StorageServiceContract;

class ProfilesController extends Controller
{
    public function __construct(
        private StorageServiceContract $storageService,
    ) {}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $profile = null;

        if ($user->profile) {
            $profile = $user->profile;
        } else {
            $profile = $user->profile()->create($request->all());
        }

        if ($profile) {
            return response()->json([
                'profile' => $profile,
                'message' => trans('messages.general_create'),
            ]);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'profile' => $user->profile,
        ]);
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

        if ($user->profile) {
            if ($user->profile()->update($request->all())) {
                return response()->json([
                    'message' => trans('messages.general_update'),
                ]);
            }
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 422);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $user = $request->user();

        if ($user->profile) {
            if ($user->profile()->delete()) {
                return response()->json([
                    'message' => trans('messages.general_destroy'),
                ]);
            }
        }
    }

    /**
     * Upload the course cover.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function upload(Request $request)
    {
        $request->validate([
            'photo' => 'file|mimes:jpeg,png|max:3000',
        ]);

        $user = $request->user();

        if (!$user->profile) {
            return response()->json([
                'error' => trans('messages.general_error'),
            ], 422);
        }

        if ($user->profile->photo) {
            $this->storageService->destroy($user->profile->photo);
        }
        
        $photo = $this->storageService->upload($request, 'photo', 'profiles');

        if ($user->profile()->update(['photo' => $photo])) {
            $user->profile->photo = $photo;
            return response()->json([
                'profile' => $user->profile,
                'message' => trans('messages.general_create'),
            ]);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 422);
    }
}
