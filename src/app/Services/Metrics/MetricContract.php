<?php

namespace App\Services\Metrics;

interface MetricContract
{
    public function counter(string $name, array $labels): void;

    public function histogram(string $name, int $value, array $labels): void;
}
