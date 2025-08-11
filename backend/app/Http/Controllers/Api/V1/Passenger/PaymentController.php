<?php

namespace App\Http\Controllers\Api\V1\Passenger;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function createCardSetupSession(Request $request)
    {
        $checkoutSession = $this->paymentService->createCardSetupCheckoutSession($request->user());
        return response_success('Card setup session created.', ['setup_url' => $checkoutSession->url]);
    }

    public function topUpWithSavedCard(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);
        $this->paymentService->chargeSavedCard($request->user(), $request->amount);
        return response_success('Top-up successful.');
    }

    public function requestRefund(Request $request)
    {
        $request->validate(['charge_id' => 'required|string']);
        $this->paymentService->refundCharge($request->user(), $request->charge_id);
        return response_success('Refund processed successfully.');
    }
}
