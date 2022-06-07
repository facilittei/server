<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\AddressRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Address;

class AddressesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return Auth::user()->addresses;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\AddressRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(AddressRequest $request)
    {
        $req = $request->all();
        $req['user_id'] = Auth::user()->id;
        $address = Address::create($req);
        if ($address) {
            return response()->json([
                'address' => $address,
                'message' => trans('messages.general_create'),
            ], 201);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 422);
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
        $user = $request->user();
        $address = Address::findOrFail($id);
        $req = $request->all();
        $req['user_id'] = Auth::user()->id;
        if ($user->can('update', $address)) {
            if ($address->update($req)) {
                return response()->json([
                    'address' => $address,
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
     * @param  int  $id
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $address = Address::findOrFail($id);

        if ($user->can('delete', $address)) {
            if ($address->delete()) {
                return response('', 204);
            }
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 422);
    }
}
