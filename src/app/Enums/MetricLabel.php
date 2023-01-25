<?php

namespace App\Enums;

class MetricLabel
{
    const METRICS = [
        'payment_request_duration_seconds' => 'The request duration for a payment transaction',
        'payment_status_counter' => 'The payment status counter',
        'payment_checkout_request_duration_seconds' => 'The checkout request duration for a payment redirect',
        'payment_hook_status_counter' => 'The payment webhook status counter',
    ];
}
