<?php

namespace App\Http\Controllers\Payments;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Mail\OrderMail;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use App\Services\Metrics\MetricContract;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __construct(private MetricContract $metricService)
    {
    }

    /**
     * Notify payment.
     *
     * @param  array  $req
     * @param  \App\Models\User  $user
     * @param  \App\Models\Order  $order
     * @param  \App\Models\Course  $course
     * @param  string|float  $start
     * @return void
     */
    private function notify(
        User $user,
        Order $order,
        Course $course,
        bool $successful,
    ): void {
        try {
            if ($successful) {
                Mail::to($user->email)->queue(
                    new OrderMail($order, $course, $user, true),
                );
                $this->metricService->counter(
                    'payment_hook_status_counter',
                    ['keys' => ['provider', 'status'], 'values' => ['stripe', 'successful']],
                );
            } else {
                Mail::to($user->email)->queue(
                    new OrderMail($order, $course, $user, false),
                );
                $this->metricService->counter(
                    'payment_hook_status_counter',
                    ['keys' => ['provider', 'status'], 'values' => ['stripe', 'failed']],
                );
            }
        } catch (Exception $e) {
            Log::error('checkout controller mailer failed', [
                'order_id' => $order->id,
                'err_code' => $e->getCode(),
                'err_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Stripe Webhook handler.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @see
     * https://stripe.com/docs/payments/checkout/fulfill-orders
     */
    public function handle(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));
        $payload = $request->getContent();
        $sigHeader = $request->header('stripe-signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);
        } catch (\UnexpectedValueException $e) {
            return response()->json([
                'error' => trans('messages.general_error'),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (SignatureVerificationException $e) {
            return response()->json([
                'error' => trans('auth.unauthorized'),
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($event->type == 'checkout.session.completed') {
            $session = Session::retrieve([
                'id' => $event->data->object->id,
            ]);

            $order = Order::findOrFail($session->client_reference_id);
            $course = $order->course;
            $user = $order->user;
            $course->students()->syncWithoutDetaching($user->id);

            $status = OrderStatus::SUCCEED->value;
            $order->histories()->create(['status' => $status]);

            $this->notify($user, $order, $course, true);
        }
    }
}
