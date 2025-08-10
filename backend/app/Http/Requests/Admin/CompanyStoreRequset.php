<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;

class CompanyStoreRequset extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone_number' => 'required|string|max:20',
            'password' => 'required|string|min:6|confirmed',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240', // 10MB
            'company_name' => 'required|string|max:255|unique:companies,company_name',
            'contact_email' => 'required|email|max:255|unique:companies,contact_email',
            'address' => 'nullable|string|max:255',
            'subdomain' => 'nullable|string|max:255|unique:companies,subdomain',
            'status' => 'nullable|in:active,pending,suspended',
            'system_access' => 'nullable|array',
            'system_access.*' => 'string|exists:permissions,name',
        ];
    }
}
