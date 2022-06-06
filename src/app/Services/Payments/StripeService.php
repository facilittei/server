<?php

namespace App\Services\Payments;

use Stripe\Charge;
use Stripe\Stripe;

class StripeService implements PaymentServiceContract
{
    public function charge(array $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $created = Charge::create([
            'amount' => $this->priceInCents($request['total']),
            'currency' => 'BRL',
            'source' => $request['credit_card'],
            'description' => $request['description'],
        ]);
        return $created;
    }

    /**
     * Apply price transformation to be based on cents
     * 
     * @param int $price
     * @return int
     */
    private function priceInCents($price)
    {
        return $price * 100;
    }
}