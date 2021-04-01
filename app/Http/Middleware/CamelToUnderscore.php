<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CamelToUnderscore
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
        $request->replace($this->transform($request->all()));
        return $next($request);
    }

    /**
     * Transform array keys from camelCase to underscore
     *
     * @param array $content
     * @return array
     */
    public function transform($content): array
    {
        if (!$content) {
            return [];
        }

        $result = [];

        foreach ($content as $key => $value) {
            if (!is_array($content[$key])) {
                $result[$this->toUnderscore($key)] = $content[$key];
            } else {
                $inner = $this->transform($content[$key]);
                foreach ($inner as $k => $v) {
                    $result[$this->toUnderscore($key)][$this->toUnderscore($k)] = $inner[$k];
                }
            }
        }

        return $result;
    }

    /**
     * Convert camelCase based to underscore
     *
     * @param string $key
     * @return string
     */
    public function toUnderscore(?string $key): string
    {
        if (!$key) {
            return '';
        }

        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $key));
    }
}
