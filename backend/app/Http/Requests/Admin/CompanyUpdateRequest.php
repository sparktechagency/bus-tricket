<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class CompanyUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Resolve route parameter for the current company id
        $companyId = $this->route('company') ?? $this->route('companies') ?? $this->id;

        return [
            'company_name' => [
                'sometimes', 'string', 'max:255',
                Rule::unique('companies', 'company_name')->ignore($companyId),
            ],
            'contact_email' => [
                'sometimes', 'email', 'max:255',
                Rule::unique('companies', 'contact_email')->ignore($companyId),
            ],
            'address' => ['sometimes', 'nullable', 'string', 'max:255'],
            'subdomain' => [
                'sometimes', 'nullable', 'string', 'max:255',
                Rule::unique('companies', 'subdomain')->ignore($companyId),
            ],
            'status' => ['sometimes', Rule::in(['active', 'pending', 'suspended'])],

            // Optional: update associated system access permissions by name
            'system_access' => ['sometimes', 'array'],
            'system_access.*' => ['string', 'exists:permissions,name'],
        ];
    }
}

