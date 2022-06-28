<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\Payments\PaymentServiceContract;
use App\Services\Metrics\MetricContract;
use App\Enums\OrderStatus;
use App\Models\Course;
use App\Mail\OrderMail;
use App\Enums\Fee;

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

        $user = Auth::user();
        if (Order::hasBought($course->id, $user->id)) {
            return response()->json([
                'error' => trans('messages.order_course_already_bought'),
            ], 400);
        }

        $req['total'] = $course->price;
        $req['description'] = $course->title;
        $req['name'] = $user->name;
        $req['email'] = $user->email;
        $order = Order::store($req, $user->id);
        $req['order_id'] = $order->id;
        $order->histories()->create(['status' => OrderStatus::STATUS['STARTED']]);
        $order->fees()->create([
            'percentage' => Fee::TOTAL['PERCENTAGE'],
            'transaction' => Fee::TOTAL['TRANSACTION'],
        ]);
        $order->items()->createMany([
            [
                'course_id' => $course->id,
                'title' => $course->title,
                'price' => $course->price,
            ]
        ]);

        $successful = false;
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

            $successful = true;
            $this->metricService->histogram(
                'payment_request_duration_seconds',
                microtime(true) - $start,
                ['keys' => ['provider'], 'values' => ['stripe']],
            );
        } catch (Exception $e) {
            $order->histories()->create(['status' => OrderStatus::STATUS['FAILED']]);
            Log::critical('checkout controller store failed', [
                'order_id' => $order->id,
                'err_code' => $e->getCode(),
                'err_message' => $e->getMessage(),
            ]);
        }

        try {
            if ($successful) {
                Mail::to(Auth::user()->email)->queue(
                    new OrderMail($order, $course, Auth::user(), true),
                );
                $this->metricService->counter(
                    'payment_status_counter', 
                    ['keys' => ['provider', 'status'], 'values' => ['stripe', 'successful']],
                );
            } else {
                Mail::to(Auth::user()->email)->queue(
                    new OrderMail($order, $course, Auth::user(), false),
                );
                $this->metricService->counter(
                    'payment_status_counter', 
                    ['keys' => ['provider', 'status'], 'values' => ['stripe', 'failed']],
                );
            }
        } catch(Exception $e) {
            Log::critical('checkout controller mailer failed', [
                'order_id' => $order->id,
                'err_code' => $e->getCode(),
                'err_message' => $e->getMessage(),
            ]);
        }

        if ($successful) {
            return response()->json([
                'order_id' => $order->id,
            ]);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 500);
    }
}
