<?php

namespace App\Http\Controllers\Payments;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Mail\OrderMail;
use App\Models\Course;
use App\Models\Order;
use App\Models\User;
use App\Services\Metrics\MetricContract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\Exception\SignatureVerificationException;

class StripeWebhookController extends Controller
{
    public function __construct(private MetricContract $metricService)
    {
    }

    /**
     * Notify payment.
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
    ): void {
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
        } catch (Exception $e) {
            Log::critical('checkout controller mailer failed', [
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
            ], 422);
        } catch (SignatureVerificationException $e) {
            return response()->json([
                'error' => trans('auth.unauthorized'),
            ], 401);
        }

        if ($event->type == 'checkout.session.completed') {
            $session = Session::retrieve([
                'id' => $event->data->object->id
            ]);

            $course = Course::findOrFail($session->metadata->course_id);
            $user = User::where('email', $session->customer_details->email)->first();
            $course->students()->syncWithoutDetaching($user->id);

            $status = OrderStatus::SUCCEED->value;
            $this->metricService->counter(
                'payment_status_counter',
                ['keys' => ['provider', 'status'], 'values' => ['stripe', $status]],
            );

            $order = Order::findOrFail($session->client_reference_id);
            $order->histories()->create(['status' => $status]);

            $this->notify($user, $order, $course, true);
            
            // Fulfill the purchase...
            error_log("Fulfilling order...");
            error_log($session->customer_details->email);
        }
    }
}
