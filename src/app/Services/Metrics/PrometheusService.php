<?php

namespace App\Services\Metrics;

use App\Enums\MetricLabel;

class PrometheusService implements MetricContract
{
    /**
     * A counter is a cumulative metric that represents
     * a single monotonically increasing counter
     * whose value can only increase or be reset to zero on restart.
     *
     * @see https://prometheus.io/docs/concepts/metric_types/
     *
     * @param  string  $name
     * @param  array  $labels
     * @return void
     */
    public function counter(string $name, array $labels): void
    {
        $counter = app('prometheus')->getOrRegisterCounter(
            $name,
            MetricLabel::METRICS[$name],
            $labels['keys'],
        );
        $counter->inc($labels['values']);
    }

    /**
     * A histogram samples observations
     * (usually things like request durations or response sizes)
     * and counts them in configurable buckets.
     *
     * @see https://prometheus.io/docs/concepts/metric_types/
     *
     * @param  string  $name
     * @param  array  $labels
     * @param  callable  $callback
     * @return void
     */
    public function histogram(string $name, array $labels, callable $callback): void
    {
        $start = microtime(true);
        $callback();
        $histogram = app('prometheus')->getOrRegisterHistogram(
            $name,
            MetricLabel::METRICS[$name],
            $labels['keys'],
            [0.1, 0.25, 0.5, 0.75, 1.0, 2.5, 5.0, 7.5, 10.0],
        );

        $histogram->observe(microtime(true) - $start, $labels['values']);
    }
}
