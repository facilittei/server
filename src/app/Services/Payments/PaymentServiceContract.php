<?php

namespace App\Services\Payments;

use Illuminate\Http\Client\Response;

interface PaymentServiceContract {
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
}