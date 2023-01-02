<?php

namespace App\Services\Payments;

use Stripe\Account;
use Stripe\AccountLink;
use Stripe\StripeClient;

class StripeService
{
    public static function client(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }

    public static function createAccount(array $params): Account
    {
        $params['type'] = 'standard';

        return self::client()->accounts->create($params);
    }

    public static function createAccountLinks(array $params): AccountLink
    {
        return self::client()->accountLinks->create($params);
    }
}
