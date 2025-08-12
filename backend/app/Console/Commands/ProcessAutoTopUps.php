<?php
namespace App\Console\Commands;

use App\Models\PassengerWallet;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class ProcessAutoTopUps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'autotopup:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process auto top-ups for passenger wallets with low balance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to process auto top-ups...');
        Stripe::setApiKey(config('services.stripe.secret'));

        // Find all wallets with auto top-up enabled and balance below the threshold
        $walletsToTopUp = PassengerWallet::where('auto_topup_enabled', true)
                                        ->where('balance', '<', 3.50)
                                        ->with(['user', 'user.paymentMethods']) // Eager load relationships
                                        ->get();

        if ($walletsToTopUp->isEmpty()) {
            $this->info('No wallets found requiring auto top-up.');
            return 0;
        }

        $this->info($walletsToTopUp->count() . ' wallet(s) found for auto top-up.');

        foreach ($walletsToTopUp as $wallet) {
            $user = $wallet->user;
            $defaultPaymentMethod = $user->paymentMethods->where('is_default', true)->first();

            if ($user->stripe_customer_id && $defaultPaymentMethod) {
                try {
                    // Use PaymentIntent to charge the user off-session
                    PaymentIntent::create([
                        'customer' => $user->stripe_customer_id,
                        'payment_method' => $defaultPaymentMethod->stripe_payment_method_id,
                        'amount' => 3500, // $35.00 in cents
                        'currency' => 'usd',
                        'off_session' => true, // This is a background charge
                        'confirm' => true,
                    ]);

                    $wallet->increment('balance', 35.00);
                    $user->transactions()->create([
                        'type' => 'AutoTopUp',
                        'amount' => 35.00,
                    ]);

                    $this->info('Successfully topped up wallet for user ID: ' . $user->id);

                } catch (\Exception $e) {
                    $this->error('Auto top-up failed for user ID: ' . $user->id . '. Reason: ' . $e->getMessage());
                    Log::error('Auto top-up failed for user ' . $user->id . ': ' . $e->getMessage());
                }
            }
        }

        $this->info('Auto top-up process finished.');
        return 0;
    }
}
