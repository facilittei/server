<?php

namespace App\Services\Payments;

use App\Enums\ErrorCode;
use App\Models\Order;
use App\Models\User;
use Exception;
use Stripe\Charge;
use Stripe\Stripe;

class StripeService implements PaymentServiceContract
{
    private ?Order $order = null;

    private ?User $customer = null;

    public function charge(array $request): mixed
    {
        if (! $this->isValid($request)) {
            throw new Exception('invalid payload for charge');
        }

        if (! $this->customer) {
            throw new Exception('customer is required');
        }

        if (! $this->order) {
            throw new Exception('order is required');
        }

        try {
            Stripe::setApiKey(config('services.stripe.secret'));

            $created = Charge::create([
                'amount' => $this->priceInCents($this->order->total),
                'currency' => 'BRL',
                'source' => $request['credit_card'],
                'description' => $request['description'],
                'metadata' => [
                    'order_id' => $this->order->id,
                    'name' => $this->customer->name,
                    'email' => $this->customer->email,
                ],
            ]);

            return $created;
        } catch(Exception $e) {
            throw new Exception(
                'stripe checkout charge',
                ErrorCode::PAYMENT_CHARGE_TRANSACTION->value,
                $e,
            );
        }
    }

    public function isValid(array $request): bool
    {
        if (! isset($request['credit_card']) || $request['credit_card'] == '') {
            return false;
        }

        if (! isset($request['description']) || $request['description'] == '') {
            return false;
        }

        return true;
    }

    public function order(Order $order): void
    {
        $this->order = $order;
    }

    public function customer(User $user): void
    {
        $this->customer = $user;
    }

    public function priceInCents($price): int
    {
        return $price * 100;
    }
}
