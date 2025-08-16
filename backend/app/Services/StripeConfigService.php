<?php
namespace App\Services;

use Stripe\Stripe;
use Exception;

class StripeConfigService
{
    /**
     * Sets the Stripe API key based on the current tenant's settings.
     * Throws an exception if the key is not configured.
     */
    public function setApiKeyForCurrentTenant(): void
    {
        $company = tenant();
        // dd($company);

        if (!$company || !$company->stripe_secret_key || !$company->stripe_publishable_key || !$company->stripe_webhook_secret) {
            throw new Exception('Stripe payment is not configured for this company. Please contact the administrator.');
        }

        Stripe::setApiKey($company->stripe_secret_key);
    }
}
