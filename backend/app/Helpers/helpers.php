<?php
// app/Helpers/helpers.php
if (!function_exists('tenant')) {
    /**
     * Get the current tenant instance, or a property from the tenant.
     */
    function tenant(?string $key = null)
    {
        if (app()->has('current_tenant')) {
            $company = app('current_tenant'); // This should be set by the IdentifyCompany middleware
            if ($key) {
                return data_get($company, $key);
            }
            return $company;
        }
        return null;
    }
}
