<?php
namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IdentifyCompany
{
    public function handle(Request $request, Closure $next)
    {
        $company = null;

        // Check if the user is authenticated and has a company_id
        if (Auth::check() && Auth::user()->company_id) {
            $company = Company::find(Auth::user()->company_id);
        }
        // dd(Auth::user());

        // If the company is not found, check for the X-Company-ID header
        if (!$company && $request->hasHeader('X-Company-ID')) {
            $companyId = $request->header('X-Company-ID');
            $company = Company::find($companyId);
        }
        // If no company is found, return an error response
        if (!$company) {
            return response_error(
                'Company not identified. Please ensure the X-Company-ID header is provided and valid.',
                [],
                400
            );
        }

        // Set the company in the application context
        if ($company) {
            app()->instance('currentCompany', $company);
        }

        return $next($request);
    }
}
