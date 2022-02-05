<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;

interface PaymentServiceContract
{
    /**
     * Authenticate with third-party payment provider.
     * 
     * @return \Illuminate\Http\Client\Response
     */
    public function authenticate(): Response;

    /**
     * Authorization access token.
     * 
     * @return string
     */
    public function getAccessToken(): string;

    /**
     * New charge request.
     * 
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Client\Response
     */
    public function charge(Request $request): Response;
}
