<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class EnsureEmailIsValid
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $redirectToRoute
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (! $request->user($guard)) {
            return response()->json([
                'message' => trans('auth.unauthorized'),
                'errors' => [
                    'main' => [trans('auth.access_token_invalid')],
                ],
            ], 401);
        }

        if (! $request->user($guard)->hasVerifiedEmail()) {
            return response()->json([
                'message' => trans('auth.unauthorized'),
                'errors' => [
                    'main' => [trans('auth.email_not_confirmed')],
                ],
            ], 403);
        }

        $this->auth->shouldUse($guard);

        return $next($request);
    }
}
