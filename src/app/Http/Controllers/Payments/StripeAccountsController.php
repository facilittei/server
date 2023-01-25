<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\PaymentPlatform;
use App\Services\Payments\StripeService;
use Illuminate\Support\Facades\Auth;

class StripeAccountsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $user = Auth::user();
        $paymentPlatform = $user->paymentPlatforms->where('name', 'Stripe')->first();

        if (!$paymentPlatform) {
            $account = StripeService::createAccount([
                'email' => $user->email,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
            ]);

            PaymentPlatform::firstOrCreate([
                'user_id' => $user->id,
                'reference_id' => $account->id,
                'name' => 'Stripe',
            ]);
        } else {
            $account = StripeService::client()->accounts->retrieve($paymentPlatform->reference_id);
        }

        $accountLink = StripeService::createAccountLinks([
            'account' => $account->id,
            'refresh_url' => config('services.stripe.refresh_url'),
            'return_url' => config('services.stripe.return_url'),
            'type' => 'account_onboarding',
        ]);

        return response()->json(['url' => $accountLink->url]);
    }
}
