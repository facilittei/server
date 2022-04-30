<?php

namespace App\Services\Payments;

interface PaymentServiceContract
{
    /**
     * New charge request.
     * 
     * @param  array $request
     * @return mixed
     */
    public function charge(array $request);
}
