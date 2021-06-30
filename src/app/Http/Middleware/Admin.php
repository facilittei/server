<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $isAdmin = $request->user()->role == 'ADMIN_CONSOLE';

        if (!$isAdmin) {
            return response()->json([
                'message' => trans('auth.unauthorized'),
                'errors' => [
                    'main' => [trans('auth.access_token_invalid')],
                ]
            ], 401);
        }
        
        return $next($request);
    }
}
