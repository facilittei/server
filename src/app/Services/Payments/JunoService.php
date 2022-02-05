<?php

namespace App\Services\Payments;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class JunoService implements PaymentServiceContract
{
    /**
     * Authenticate to get access token.
     * 
     * @return \Illuminate\Http\Client\Response
     */
    public function authenticate(): Response
    {
        try {
            return Http::juno()
                ->withHeaders([
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Authorization' => 'Basic ' . config('services.juno.authorization_basic'),
                ])
                ->post('/authorization-server/oauth/token?grant_type=client_credentials');
        } catch (Exception $e) {
            Log::error('Juno authentication failed', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
        }
    }

    /**
     * Access token to be used in resource requests.
     * 
     * We cache the access token for 3000 seconds (50 minutes)
     * avoiding to request for a new access token every request.
     * Also Juno has a hard limit of 1 hour expiration.
     * 
     * @return string
     */
    public function getAccessToken(): string
    {
        $access_token = Cache::remember('access_token', 3000, function () {
            $response = $this->authenticate();
            if ($response->successful()) {
                return $response->json()['access_token'];
            }
        });
        return $access_token;
    }
}
