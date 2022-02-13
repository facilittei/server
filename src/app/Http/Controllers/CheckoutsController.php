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
        $course = Course::findOrFail($req['courses'][0]['id']);

        $req['total'] = $course->price;
        $req['amount'] = $course->price;
        $order = Order::store($req, Auth::user()->id);
        $order->histories()->create(['status' => OrderStatus::STATUS['STARTED']]);
        $order->items()->createMany([
            [
                'course_id' => $course->id,
                'title' => $course->title,
                'price' => $course->price,
            ]
        ]);
        $response = $this->paymentService->charge($req);

        try {
            $resp = $response->json();
            $status = OrderStatus::STATUS[$resp['payments'][0]['status']] ?? OrderStatus::STATUS['PENDING'];
            $order->histories()->create([
                'status' => $status,
                'reference' => $resp['payments'][0]['chargeId'] . '|' . $resp['payments'][0]['id'],
                'description' => $resp['payments'][0]['fee'],
            ]);

            $order->update(['transaction_ref' => $resp['transactionId']]);
            if ($status == OrderStatus::STATUS['CONFIRMED'] || $status == OrderStatus::STATUS['PAID']) {
                $course->students()->syncWithoutDetaching(Auth::user()->id);
            }

            $this->metricService->histogram(
                'payment_request_duration_seconds',
                microtime(true) - $start,
                ['keys' => ['provider'], 'values' => ['juno']],
            );

            return response()->json([
                'order_id' => $order->id,
                'status' => $status,
            ], $response->status());
        } catch (Exception $e) {
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

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }
}
