<?php

namespace App\Services\Payments;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
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

    /**
     * Creates a new charge by a two step process
     * 1. Creates and register the charge
     * 2. Sends payment details to be processed
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Client\Response
     */
    public function charge(Request $request): Response
    {
        $token = $this->getAccessToken();
        $response = Http::juno()
            ->withToken($token)
            ->post('/api-integration/charges', $this->chargeCreateRequest($request));

        if ($response->failed()) {
            return $response;
        }

        $charge_id = $response->json()['content'][0]['id'];
        $response = Http::juno()
            ->withToken($token)
            ->post('/api-integration/payments', $this->chargePayRequest($request, $charge_id));
        return $response;
    }

    /**
     * Create charge payload request.
     * 
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    private function chargeCreateRequest(Request $request): array
    {
        return [
            'charge' => [
                'description' => $request->input('description'),
                'amount' => $request->input('amount'),
                'paymentTypes' => ['CREDIT_CARD'],
            ],
            'billing' => [
                'name' => $request->input('customer.name'),
                'document' => $request->input('customer.document'),
                'email' => $request->input('customer.email'),
                'address' => $this->chargeAddressRequest($request),
            ],
        ];
    }

    /**
     * Pay charge payload request.
     * 
     * @param string $charge_id;
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    private function chargePayRequest(Request $request, string $charge_id): array
    {
        return [
            'chargeId' => $charge_id,
            'billing' => [
                'email' => $request->input('customer.email'),
                'address' => $this->chargeAddressRequest($request),
            ],
            'creditCardDetails' => [
                'creditCardHash' => $request->input('credit_card.hash'),
            ],
        ];
    }

    /**
     * Charge address payload request.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    private function chargeAddressRequest(Request $request): array
    {
        return [
            'street' => $request->input('customer.address.street'),
            'number' => $request->input('customer.address.number'),
            'city' => $request->input('customer.address.city'),
            'state' => $request->input('customer.address.state'),
            'postCode' => $request->input('customer.address.post_code'),
        ];
    }
}
