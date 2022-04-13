<?php

namespace App\Enums;

class OrderStatus
{
    const STATUS = [
        'BANK_PAID_BACK' => 'bank_paid_back',
        'CONFIRMED' => 'confirmed',
        'CUSTOMER_PAID_BACK' => 'customer_paid_back',
        'DECLINED' => 'decline',
        'FAILED' => 'failed',
        'NOT_AUTHORIZED' => 'not_authorized',
        'PAID' => 'paid',
        'PARTIALLY_REFUNDED' => 'partially_refunded',
        'PENDING' => 'pending',
        'STARTED'   => 'started',
    ];
}
