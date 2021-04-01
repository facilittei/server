<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UnderscoreToCamel
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
        $response = $next($request);

        if ($response->headers->get('content-type') === 'application/json') {
            $content = $this->toArray($response->getContent());
            $response->setContent($this->toJSON($content));
        }

        return $response;
    }

    /**
     * Convert string content (json) to array
     *
     * @param string $content
     * @return array
     */
    public function toArray(string $content): array
    {
        return json_decode($content, true);
    }

    /**
     * Serialize array into JSON
     *
     * @param array $response
     * @return string
     */
    public function toJSON(array $content): string
    {
        return json_encode($this->transform($content));
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
                $result[$this->toCamel($key)] = $content[$key];
            } else {
                $inner = $this->transform($content[$key]);
                foreach ($inner as $k => $v) {
                    $result[$this->toCamel($key)][$this->toCamel($k)] = $inner[$k];
                }
            }
        }

        return $result;
    }

    /**
     * Convert underscore based to camelCase
     *
     * @param string $key
     * @return string
     */
    public function toCamel(?string $key): string
    {
        if (!$key) {
            return '';
        }

        return lcfirst(str_replace('_', '', ucwords($key, '_')));
    }
}
