<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Models\Order;
use App\Services\Payments\PaymentServiceContract;
use Illuminate\Support\Facades\Auth;
use App\Enums\OrderStatus;

class CheckoutsController extends Controller
{
    public function __construct(
        private PaymentServiceContract $paymentService,
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
        $order = Order::store($request, Auth::user()->id);
        $order->histories()->create(['status' => OrderStatus::STATUS['STARTED']]);
        $response = $this->paymentService->charge($request);

        $resp = $response->json();
        $order->histories()->create([
            'status' => OrderStatus::STATUS[$resp['payments'][0]['status']],
            'reference' => $resp['payments'][0]['chargeId'] . '|' . $resp['payments'][0]['id'],
        ]);
        return response()->json($response->json(), $response->status());
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
