<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Locale
{
    /**
     * The availables languages.
     *
     * @array $languages
     */
    protected $languages = ['en', 'pt_BR'];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('locale')) {
            session()->put('locale', $request->getPreferredLanguage($this->languages));
        }

        app()->setLocale(session()->get('locale'));

        return $next($request);
    }
}
