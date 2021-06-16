<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
