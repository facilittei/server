<?php

namespace App\Enums;

class OrderStatus
{
    const STATUS = [
        'STARTED' => 'started',
        'SUCCEED' => 'succeed',
        'PENDING' => 'pending',
        'FAILED'   => 'failed',
    ];
}
