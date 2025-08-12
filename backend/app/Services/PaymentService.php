<?php
// app/Services/PaymentService.php
namespace App\Services;

use App\Models\User;
use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Refund;
use Stripe\PaymentIntent;

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
            'success_url' => 'https://yourapp.com/payment-method/success',
            'cancel_url' => 'https://yourapp.com/payment-method/cancel',
        ]);
    }

    /**
     * Charges a user's saved card for a manual top-up using Payment Intents.
     */
    public function chargeSavedCard(User $user, float $amount): PaymentIntent
    {
        $customer = $this->getOrCreateStripeCustomer($user);
        $defaultPaymentMethod = $user->paymentMethods()->where('is_default', true)->first();

        if (!$defaultPaymentMethod) {
            throw new \Exception('No default payment method found.');
        }

        $amountInCents = round($amount * 100);

        // First, create a pending transaction record in database.
        $transaction = $user->transactions()->create([
            'company_id' => $user->company_id,
            'type' => 'TopUp',
            'amount' => $amount,
            'status' => 'pending',
        ]);

        $paymentIntent = PaymentIntent::create([
            'customer' => $customer->id,
            'payment_method' => $defaultPaymentMethod->stripe_payment_method_id,
            'amount' => $amountInCents,
            'currency' => 'usd',
            'off_session' => false,
            'confirm' => true,
            'metadata' => [
                'transaction_id' => $transaction->id, // Link to the transaction
            ],
            'return_url' => config('app.url'),
        ]);
        // Save the Stripe Payment Intent ID to our transaction record
        $transaction->update(['stripe_payment_intent_id' => $paymentIntent->id]);

        return $paymentIntent;
    }

    /**
     * Option 2: Creates a Payment Intent that can handle both immediate payment
     * and saving the card for future use if requested.
     */
    // public function createPaymentIntent(User $user, float $amount, bool $saveCard = false): PaymentIntent
    // {
    //     $customer = $this->getOrCreateStripeCustomer($user);
    //     $amountInCents = round($amount * 100);

    //     $transaction = $user->transactions()->create([
    //         'company_id' => $user->company_id,
    //         'type' => 'TopUp',
    //         'amount' => $amount,
    //         'status' => 'pending',
    //     ]);

    //     $paymentIntentParams = [
    //         'customer' => $customer->id,
    //         'amount' => $amountInCents,
    //         'currency' => 'usd',
    //         'metadata' => [ 'transaction_id' => $transaction->id ],
    //     ];

    //     if ($saveCard) {
    //         $paymentIntentParams['setup_future_usage'] = 'off_session';
    //     }

    //     $paymentIntent = PaymentIntent::create($paymentIntentParams);
    //     $transaction->update(['stripe_payment_intent_id' => $paymentIntent->id]);

    //     return $paymentIntent;
    // }

    public function createPaymentCheckoutSession(User $user, float $amount, bool $saveCard = false, $defaultPaymentMethod = false): CheckoutSession | PaymentIntent
    {
        if ($defaultPaymentMethod) {
            $paymentIntent = $this->chargeSavedCard($user, $amount);
            return $paymentIntent;
        }
        $customer = $this->getOrCreateStripeCustomer($user);
        $amountInCents = round($amount * 100);

        $transaction = $user->transactions()->create([
            'company_id' => $user->company_id,
            'type' => 'TopUp',
            'amount' => $amount,
            'status' => 'pending',
        ]);

        $checkoutSessionParams = [
            'customer' => $customer->id,
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => ['name' => 'Wallet Top-up'],
                    'unit_amount' => $amountInCents,
                ],
                'quantity' => 1,
            ]],
            'metadata' => [
                'transaction_id' => $transaction->id,
            ],
            'success_url' => 'https://yourapp.com/payment/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'https://yourapp.com/payment/cancel',
        ];

        // If saving the card, set up future usage for automatic top-ups
        if ($saveCard) {
            $checkoutSessionParams['payment_intent_data'] = [
                'setup_future_usage' => 'off_session',
            ];
        }

        $session = CheckoutSession::create($checkoutSessionParams);
        // dd($session);
        // $transaction->update(['stripe_payment_intent_id' => $session->payment_intent]);
        // dd($transaction);

        return $session;
    }





    public function refundCharge(User $user, string $stripeChargeId): void
    {
        try {
            $refund = Refund::create(['charge' => $stripeChargeId]);
            $refundAmount = $refund->amount / 100;

            $user->wallet->decrement('balance', $refundAmount);
            $user->transactions()->create([
                'company_id' => $user->company_id,
                'type' => 'Refund',
                'amount' => -$refundAmount,
                'stripe_charge_id' => $stripeChargeId,
            ]);
        } catch (\Exception $e) {
            throw new \Exception('Refund failed: ' . $e->getMessage());
        }
    }

    //save card information
    // public function savePaymentMethodFromWebhook(string $customerId, string $stripePaymentMethodId): void
    // {
    //     $user = User::where('stripe_customer_id', $customerId)->first();
    //     if (!$user) {
    //         // If user is not found, we can't save the card.
    //         return;
    //     }

    //     $stripePaymentMethod = PaymentMethod::retrieve($stripePaymentMethodId);

    //     // Set any other existing cards for this user to not be the default.
    //     $user->paymentMethods()->update(['is_default' => false]);

    //     // Save the new card details to our database and mark it as the default.
    //     $user->paymentMethods()->create([
    //         'stripe_payment_method_id' => $stripePaymentMethod->id,
    //         'card_brand' => $stripePaymentMethod->card->brand,
    //         'last_four' => $stripePaymentMethod->card->last4,
    //         'is_default' => true,
    //     ]);
    // }

    /**
     * Saves card details in our database. This is called by the webhook.
     */
    public function savePaymentMethod(User $user, string $stripePaymentMethodId): void
    {
        $stripePaymentMethod = PaymentMethod::retrieve($stripePaymentMethodId);
        $fingerprint = $stripePaymentMethod->card->fingerprint;

        // Set any other existing cards for this user to not be the default.
        $user->paymentMethods()->update(['is_default' => false]);

        $paymentMethod = $user->paymentMethods()->firstOrCreate(
            ['fingerprint' => $fingerprint],
            [
                'stripe_payment_method_id' => $stripePaymentMethod->id,
                'card_brand' => $stripePaymentMethod->card->brand,
                'last_four' => $stripePaymentMethod->card->last4,
            ]
        );

        // Mark this card as the default.
        $paymentMethod->update(['is_default' => true]);
    }
}
