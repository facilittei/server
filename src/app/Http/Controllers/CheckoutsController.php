<?php

namespace App\Http\Controllers;

use Exception;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\Payments\PaymentServiceContract;
use Illuminate\Support\Facades\Auth;
use App\Enums\OrderStatus;
use App\Models\Course;
use App\Services\Metrics\MetricContract;
use Illuminate\Support\Facades\Log;

class CheckoutsController extends Controller
{
    public function __construct(
        private PaymentServiceContract $paymentService,
        private MetricContract $metricService,
    ) {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CheckoutRequest $request)
    {
        $start = microtime(true);
        $req = $request->all();
        $course = Course::findOrFail($req['course_id']);

        $req['total'] = $course->price;
        $req['description'] = $course->title;
        $order = Order::store($req, Auth::user()->id);
        $order->histories()->create(['status' => OrderStatus::STATUS['STARTED']]);
        $order->items()->createMany([
            [
                'course_id' => $course->id,
                'title' => $course->title,
                'price' => $course->price,
            ]
        ]);

        try {
            $resp = $this->paymentService->charge($req);
            $status = $resp['status'];

            $order->histories()->create([
                'status' => $status,
                'reference' => $resp['id'],
                'description' => $course->title,
            ]);

            $order->update(['reference' => $resp['id']]);
            if ($status == OrderStatus::STATUS['SUCCEED']) {
                $course->students()->syncWithoutDetaching(Auth::user()->id);
            }

            $this->metricService->histogram(
                'payment_request_duration_seconds',
                microtime(true) - $start,
                ['keys' => ['provider'], 'values' => ['stripe']],
            );

            return response()->json([
                'order_id' => $order->id,
            ]);
        } catch (Exception $e) {
            $order->histories()->create(['status' => OrderStatus::STATUS['FAILED']]);
            Log::critical('checkout controller store failed', [
                'order_id' => $order->id,
                'err_code' => $e->getCode(),
                'err_message' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 500);
    }
}
