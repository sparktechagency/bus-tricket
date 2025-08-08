<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class DriverStoreRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         $companyId = tenant('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // 'password' => ['required', 'string', 'min:6', 'confirmed'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            //  Check for uniqueness only within the current company
            'staff_number' => ['required', 'string', 'max:50', Rule::unique('drivers')->where('company_id', $companyId)],
            'pin_code' => ['required', 'string', 'min:3', 'max:5'],
            // Apply the same fix for license_number as it can also be the same across companies
            'license_number' => ['required', 'string', 'max:100', Rule::unique('drivers')->where('company_id', $companyId)],
            'license_expiry_date' => ['required', 'date'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }
}
