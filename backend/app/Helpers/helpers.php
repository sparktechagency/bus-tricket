<?php
use Spatie\Multitenancy\Models\Tenant;

if (!function_exists('tenant')) {
    /**
     * Get the current tenant or a specific attribute.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed|null
     */
    function tenant(?string $key = null, mixed $default = null): mixed
    {
        $tenant = Tenant::current();

        if (!$tenant) {
            return null;
        }

        if ($key) {
            return data_get($tenant, $key, $default);
        }

        return $tenant;
    }
}
