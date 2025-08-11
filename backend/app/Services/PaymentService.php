<?php
// app/Services/PaymentService.php
namespace App\Services;

use App\Models\User;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Charge;
use Stripe\Refund;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function getOrCreateStripeCustomer(User $user): Customer
    {
        if ($user->stripe_customer_id) {
            return Customer::retrieve($user->stripe_customer_id);
        }
        $customer = Customer::create(['email' => $user->email, 'name' => $user->name]);
        $user->update(['stripe_customer_id' => $customer->id]);
        return $customer;
    }

    public function createCardSetupCheckoutSession(User $user): CheckoutSession
    {
        $customer = $this->getOrCreateStripeCustomer($user);
        return CheckoutSession::create([
            'customer' => $customer->id,
            'mode' => 'setup',
            'payment_method_types' => ['card'],
            'success_url' => '[https://yourapp.com/payment-method/success](https://yourapp.com/payment-method/success)',
            'cancel_url' => '[https://yourapp.com/payment-method/cancel](https://yourapp.com/payment-method/cancel)',
        ]);
    }

    public function chargeSavedCard(User $user, float $amount): void
    {
        $customer = $this->getOrCreateStripeCustomer($user);
        $defaultPaymentMethod = $user->paymentMethods()->where('is_default', true)->first();

        if (!$defaultPaymentMethod) {
            throw new \Exception('No default payment method found.');
        }

        $amountInCents = round($amount * 100);

        $charge = Charge::create([
            'customer' => $customer->id,
            'payment_method' => $defaultPaymentMethod->stripe_payment_method_id,
            'amount' => $amountInCents,
            'currency' => 'usd',
            'off_session' => false,
            'confirm' => true,
        ]);

        $user->wallet->increment('balance', $amount);
        $user->transactions()->create([
            'type' => 'TopUp',
            'amount' => $amount,
            'stripe_charge_id' => $charge->id,
        ]);
    }

    public function refundCharge(User $user, string $stripeChargeId): void
    {
        $refund = Refund::create(['charge' => $stripeChargeId]);
        $refundAmount = $refund->amount / 100;

        $user->wallet->decrement('balance', $refundAmount);
        $user->transactions()->create([
            'type' => 'Refund',
            'amount' => -$refundAmount,
            'stripe_charge_id' => $stripeChargeId,
        ]);
    }
}
