<?php

namespace App\Enums;

enum OrderStatus: string
{
    case STARTED = 'started';
    case SUCCEED = 'succeeded';
    case PENDING = 'pending';
    case FAILED = 'failed';
}
