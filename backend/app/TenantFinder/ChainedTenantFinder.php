<?php

namespace App\TenantFinder;

use Illuminate\Http\Request;
use App\Models\Company;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class ChainedTenantFinder extends TenantFinder
{
    /**
     * This method will be called to find the current tenant.
     * It will try to find the tenant in a specific order:
     * 1. By Subdomain (for web)
     * 2. By Request Header (for API)
     * 3. By Logged-in User (for authenticated requests)
     */
    public function findForRequest(Request $request): ?IsTenant
    {
        // 1. Try to find tenant by subdomain first
        if ($tenant = $this->findBySubdomain($request)) {
            return $tenant;
        }

        // 2. If not found, try to find by request header
        if ($tenant = $this->findByRequestHeader($request)) {
            return $tenant;
        }

        // 3. If still not found, try to find by the authenticated user
        if ($tenant = $this->findByUser($request)) {
            return $tenant;
        }

        return null;
    }

    /**
     * Tries to find a tenant based on the request's host.
     */
    protected function findBySubdomain(Request $request): ?IsTenant
    {
        $host = $request->getHost();

        // This logic might need adjustment based on your domain structure.
        $subdomain = explode('.', $host)[0];

        return Company::where('subdomain', $subdomain)->first();
    }

    /**
     * Tries to find a tenant based on a request header.
     */
    protected function findByRequestHeader(Request $request): ?IsTenant
    {
        $headerName = config('multitenancy.tenant_id_request_key');

        if (! $headerName) {
            return null;
        }

        $companyId = $request->header($headerName);

        if (! $companyId) {
            return null;
        }

        return Company::find($companyId);
    }

    /**
     * Tries to find a tenant based on the currently authenticated user.
     */
    protected function findByUser(Request $request): ?IsTenant
    {
        $user = $request->user();

        if (! $user || !isset($user->company_id)) {
            return null;
        }

        return Company::find($user->company_id);
    }
}
