<?php

namespace App\TenantFinder;

use Illuminate\Http\Request;
use Spatie\Multitenancy\Contracts\Tenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

// NOTE: We are extending the base TenantFinder class from the package.
class ChainedTenantFinder extends TenantFinder
{
    /**
     * An array of tenant finder classes to be used in sequence.
     * The first finder that returns a tenant will be used.
     */
    protected array $finders = [
        // These are the built-in finders from the v4 package.
        \Spatie\Multitenancy\TenantFinder\SubdomainTenantFinder::class,
        \Spatie\Multitenancy\TenantFinder\RequestHeaderTenantFinder::class,
        \Spatie\Multitenancy\TenantFinder\UserTenantFinder::class,
    ];

    /**
     * This is the method that the package will call to find the tenant.
     * The method name is `find` in v4.
     */
    public function find(Request $request): ?Tenant
    {
        foreach ($this->finders as $finderClass) {
            /** @var TenantFinder $finder */
            $finder = app($finderClass);

            if ($tenant = $finder->find($request)) {
                return $tenant;
            }
        }

        return null;
    }
}
