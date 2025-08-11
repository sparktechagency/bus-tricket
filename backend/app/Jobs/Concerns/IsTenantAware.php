<?php
namespace App\Jobs\Concerns;

use App\Models\Company;

trait IsTenantAware
{
    public ?int $tenantId;

    /**
     * Sets the tenant ID for the job.
     * This method should be called before the job is dispatched.
     */
    public function onTenant(): self
    {
        $this->tenantId = tenant('id');
        return $this;
    }

    /**
    * Sets the tenant again using the ID when the job is running.
     */
    public function nowOnTenant(): void
    {
        if ($this->tenantId === null) {
            return;
        }

        $tenant = Company::find($this->tenantId);

        if ($tenant) {
            app()->instance('current_tenant', $tenant);
        }
    }
}
