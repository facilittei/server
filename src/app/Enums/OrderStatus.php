<?php

namespace App\Enums;

class OrderStatus
{
    const STATUS = [
        'STARTED' => 'started',
        'SUCCEED' => 'succeeded',
        'PENDING' => 'pending',
        'FAILED'   => 'failed',
    ];
}
