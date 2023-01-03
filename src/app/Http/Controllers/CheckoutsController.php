<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\Payments\PaymentServiceContract;
use App\Services\Metrics\MetricContract;
use App\Enums\OrderStatus;
use App\Models\Course;
use App\Mail\OrderMail;
use App\Models\User;
use App\Enums\Fee as FeeEnum;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;

class CheckoutsController extends Controller
{
    public function __construct(
        private PaymentServiceContract $paymentService,
        private MetricContract $metricService,
    ) {
    }

    /**
     * Charge order.
     * 
     * @param array $req
     * @param \App\Models\User $user
     * @param \App\Models\Order $order
     * @param \App\Models\Course $course
     * @param string|float $start
     * @return bool
     */
    private function charge(
        array $req, 
        User $user,
        Order $order, 
        Course $course, 
        string|float $start,
    ): bool
    {
        $successful = false;
        try {
            $this->paymentService->customer($user);
            $this->paymentService->order($order);
            $resp = $this->paymentService->charge($req);
            $status = $resp['status'];

            $order->histories()->create([
                'status' => $status,
                'reference' => $resp['id'],
                'description' => $course->title,
            ]);

            $order->update(['reference' => $resp['id']]);

            if ($status == OrderStatus::SUCCEED->value) {
                $course->students()->syncWithoutDetaching($user->id);
            }

            $successful = true;
            $this->metricService->histogram(
                'payment_request_duration_seconds',
                microtime(true) - $start,
                ['keys' => ['provider'], 'values' => ['stripe']],
            );
        } catch (Exception $e) {
            $order->histories()->create(['status' => OrderStatus::FAILED->value]);
            Log::critical('checkout controller store failed', [
                'order_id' => $order->id,
                'err_code' => $e->getCode(),
                'err_message' => $e->getMessage(),
            ]);
        }

        return $successful;
    }

    /**
     * Notify charge.
     * 
     * @param array $req
     * @param \App\Models\User $user
     * @param \App\Models\Order $order
     * @param \App\Models\Course $course
     * @param string|float $start
     * @return void
     */
    private function notify(
        User $user,
        Order $order, 
        Course $course, 
        bool $successful,
    ): void
    {
        try {
            if ($successful) {
                Mail::to($user->email)->queue(
                    new OrderMail($order, $course, $user, true),
                );
                $this->metricService->counter(
                    'payment_status_counter', 
                    ['keys' => ['provider', 'status'], 'values' => ['stripe', 'successful']],
                );
            } else {
                Mail::to($user->email)->queue(
                    new OrderMail($order, $course, $user, false),
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
    }

    /**
     * Prepare charge payload.
     * 
     * @param array $req
     * @param \App\Models\User $user
     * @param \App\Models\Course $course
     * @return array
     * @throws \Exception
     */
    private function prepare(array $req, User $user, Course $course): array
    {
        if (Order::hasBought($course->id, $user->id)) {
            throw new Exception('customer has already bought course');
        }

        $req['total'] = $course->price;
        $req['description'] = $course->title;
        $req['name'] = $user->name;
        $req['email'] = $user->email;

        return $req;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CheckoutRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CheckoutRequest $request)
    {
        $start = microtime(true);
        $req = $request->all();
        $course = Course::findOrFail($req['course_id']);

        $user = Auth::user();
        
        try {
            $req = $this->prepare($req, $user, $course);
        } catch(Exception $e) {
            return response()->json([
                'error' => trans('messages.order_course_already_bought'),
            ], 400);
        }
       
        $order = Order::store($req, $user->id);
        $req['order_id'] = $order->id;
        $order->histories()->create(['status' => OrderStatus::STARTED->value]);
        $order->fees()->create([
            'percentage' => FeeEnum::PERCENTAGE->total(),
            'transaction' => FeeEnum::TRANSACTION->total(),
        ]);
        $order->items()->createMany([
            [
                'course_id' => $course->id,
                'title' => $course->title,
                'price' => $course->price,
            ]
        ]);

        $successful = $this->charge($req, $user, $order, $course, $start);
        $this->notify($user, $order, $course, $successful);

        $total['fees'] = $order->total * (FeeEnum::PERCENTAGE->total() / 100);
        $total['fees'] += FeeEnum::TRANSACTION->total();
        $total['gross'] = $order->total;
        $total['net'] = $total['gross'] - $total['fees'];
        
        if ($successful) {
            return response()->json([
                'order_id' => $order->id,
                ...$total,
            ]);
        }

        return response()->json([
            'error' => trans('messages.general_error'),
        ], 500);
    }

    /**
     * Checkout Session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     * 
     * @see 
     * https://stripe.com/docs/checkout/quickstart
     * https://stripe.com/docs/api/checkout/sessions/object
     */
    public function stripe(Request $request)
    {
        $req = $request->only(['course_id']);
        $course = Course::findOrFail($req['course_id']);
        $user = Auth::user();

        if (Order::hasBought($course->id, $user->id)) {
            throw new Exception('customer has already bought course');
        }

        $req['total'] = $course->price;
        $req['description'] = $course->title;
        $req['name'] = $user->name;
        $req['email'] = $user->email;

        $order = Order::store($req, $user->id);
        $order->histories()->create(['status' => OrderStatus::STARTED->value]);
        $order->fees()->create([
            'percentage' => FeeEnum::PERCENTAGE->total(),
            'transaction' => FeeEnum::TRANSACTION->total(),
        ]);
        $order->items()->createMany([
            [
                'course_id' => $course->id,
                'title' => $course->title,
                'price' => $course->price,
            ]
        ]);

        Stripe::setApiKey(config('services.stripe.secret'));
        $checkoutSession = StripeSession::create([
            'client_reference_id' => $order->id,
            'mode' => 'payment',
            'customer_email' => $user->email,
            'currency' => 'BRL',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'BRL',
                        'unit_amount' => $course->price * 100,
                        'product_data' => [
                            'name' => $course->title,
                        ],
                    ],
                    'quantity' => 1,
                ]
            ],
            'success_url' => config('app.client_url') . '/checkout/success/' . $course->id,
            'cancel_url' => config('app.client_url') . '/course/' . $course->id,
        ]);

        return $checkoutSession->url;
    }
}
