<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\User;

interface PaymentServiceContract
{
    /**
     * New charge request.
     *
     * @param  array  $request
     * @return mixed
     *
     * @throws \Exception
     */
    public function charge(array $request);

    /**
     * Validate charge payload.
     *
     * @param  array  $charge
     * @return bool
     */
    public function isValid(array $charge): bool;

    /**
     * Set order information.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function order(Order $order): void;

    /**
     * Set customer information.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function customer(User $user): void;

    /**
     * Format price to be used as cents.
     *
     * @param  float|int|string  $price
     * @return int
     */
    public function priceInCents(float|int|string $price): int;
}
