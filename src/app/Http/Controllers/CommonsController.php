<?php

namespace App\Http\Controllers;

use Carbon\Carbon;

class CommonsController extends Controller
{
    /**
     * Display the application's version.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function healthcheck()
    {
        return response()->json([
            'version' => config('app.version'),
            'datetime' => Carbon::now(),
        ]);
    }
}
