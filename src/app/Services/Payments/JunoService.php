<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;

class JunoService
{
    public static function Authenticate(): Response
    {
        return Http::juno()->post('/authorization-server/oauth/token?grant_type=client_credentials');
    }
}
