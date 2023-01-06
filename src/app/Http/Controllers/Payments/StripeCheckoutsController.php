<?php

namespace App\Http\Controllers\Payments;

use App\Enums\Fee;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Order;
use App\Services\Metrics\MetricContract;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class StripeCheckoutsController extends Controller
{
    public function __construct(private MetricContract $metricService)
    {
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
    public function create(Request $request)
    {
        $this->metricService->histogram(
            'payment_checkout_request_duration_seconds',
            ['keys' => ['provider'], 'values' => ['stripe']],
            function () use ($request, &$checkoutSession) {
                $req = $request->only(['course_id']);
                $course = Course::findOrFail($req['course_id']);
                $user = Auth::user();

                if (Order::hasBought($course->id, $user->id)) {
                    return response()->json([
                        'message' => trans('messages.order_course_already_bought'),
                    ], Response::HTTP_BAD_REQUEST);
                }

                if (!count($course->user->paymentPlatforms)) {
                    return response()->json([
                        'message' => trans('messages.order_no_payment'),
                    ], Response::HTTP_BAD_REQUEST);
                }

                $pp = $course->user->paymentPlatforms->firstWhere('is_enabled', true);
                if (!$pp) {
                    return response()->json([
                        'message' => trans('messages.order_no_payment_enabled'),
                    ], Response::HTTP_BAD_REQUEST);
                }

                $req['total'] = $course->price;
                $req['description'] = $course->title;
                $req['name'] = $user->name;
                $req['email'] = $user->email;

                $status = OrderStatus::STARTED->value;
                $this->metricService->counter(
                    'payment_status_counter',
                    ['keys' => ['provider', 'status'], 'values' => ['stripe', $status]],
                );

                $order = Order::store($req, $user->id);
                $order->histories()->create(['status' => $status]);
                $order->fees()->create([
                    'percentage' => Fee::PERCENTAGE->total(),
                    'transaction' => Fee::TRANSACTION->total(),
                ]);
                $order->items()->createMany([
                    [
                        'course_id' => $course->id,
                        'title' => $course->title,
                        'price' => $course->price,
                    ],
                ]);

                Stripe::setApiKey(config('services.stripe.secret'));

                $images = $course->cover ? [$course->cover] : [];

                $checkoutSession = Session::create([
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
                                    'images' => $images,
                                ],
                            ],
                            'quantity' => 1,
                        ],
                    ],
                    'payment_intent_data' => [
                        'application_fee_amount' => 123,
                        'transfer_data' => [
                            'destination' => $pp->reference_id,
                        ],
                    ],
                    'success_url' => config('app.client_url') . '/checkout/success',
                    'cancel_url' => config('app.client_url') . '/checkout/cancel',
                ]);

                $status = OrderStatus::PENDING->value;
                $this->metricService->counter(
                    'payment_status_counter',
                    ['keys' => ['provider', 'status'], 'values' => ['stripe', $status]],
                );
                $order->histories()->create(['status' => $status]);
            },
        );

        return response()->json(['url' => $checkoutSession->url]);
    }
}
