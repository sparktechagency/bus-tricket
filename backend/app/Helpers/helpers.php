<?php
use App\Models\Company;
use Illuminate\Support\Facades\Auth;

if (!function_exists('tenant')) {
    /**
     * Get the current company (tenant) or a specific attribute.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed|null
     */
    function tenant(?string $key = null, mixed $default = null): mixed
    {

        if (Auth::check() && Auth::user()->company_id) {
            $company = Company::find(Auth::user()->company_id);
        }
       
        elseif (request()->hasHeader('X-Company-ID')) {
            $companyId = request()->header('X-Company-ID');
            $company = Company::find($companyId);
        } else {
            return null;
        }

        if (!$company) {
            return null;
        }

        if ($key) {
            return data_get($company, $key, $default);
        }

        return $company;
    }
}
