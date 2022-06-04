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
        if (Address::create($req)) {
            return response()->json([
                'message' => trans('messages.general_create'),
            ], 201);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 422);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, int $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\AddressRequest $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AddressRequest $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
