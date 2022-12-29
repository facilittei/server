<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Services\Payments\StripeService;
use Illuminate\Support\Facades\Auth;

class StripeAccounts extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        $account = StripeService::createAccount([
            'type' => 'standard',
            'email' => Auth::user()->email,
        ]);

        $accountLink = StripeService::createAccountLinks([
            'account' => $account->id,
            'refresh_url' => config('services.stripe.refresh_url'),
            'return_url' => config('services.stripe.return_url'),
            'type' => 'account_onboarding',
        ]);

        return response()->json(['url' => $accountLink->url]);
    }
}
