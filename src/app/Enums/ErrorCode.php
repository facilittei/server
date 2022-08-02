<?php

namespace App\Enums;

enum ErrorCode: int 
{
    case PAYMENT_CHARGE_TRANSACTION = 10000;
    case PAYMENT_CHARGE_PAYLOAD = 10001;
    case PAYMENT_CHARGE_CUSTOMER = 10002;
}