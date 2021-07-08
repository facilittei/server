<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommonsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (App::environment('local')) {
        return view('welcome');
    }

    return response()->json([]);
});
