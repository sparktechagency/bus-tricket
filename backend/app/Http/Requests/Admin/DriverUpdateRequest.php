<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;
use App\Models\Driver;
use Illuminate\Validation\Rule;

class DriverUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $companyId = tenant('id');
        $driverId = $this->route('driver'); // resource route param name

        $userId = null;
        if ($driverId) {
            $driver = Driver::query()->select(['id', 'user_id', 'company_id'])->find($driverId);
            $userId = $driver?->user_id;
        }

        return [
            // User fields
            'name' => ['sometimes', 'string', 'max:255'],
            'username' => [
                'sometimes', 'nullable', 'string', 'max:100',
                Rule::unique('users', 'username')->ignore($userId)
            ],
            'email' => [
                'sometimes', 'nullable', 'string', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => ['sometimes', 'nullable', 'string', 'min:6', 'confirmed'],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:20'],

            // Driver fields
            'staff_number' => [
                'sometimes', 'string', 'max:50',
                Rule::unique('drivers', 'staff_number')
                    ->where(fn ($q) => $q->where('company_id', $companyId))
                    ->ignore($driverId)
            ],
            'pin_code' => ['sometimes', 'string', 'min:3', 'max:5'],
            'license_number' => [
                'sometimes', 'string', 'max:100',
                Rule::unique('drivers', 'license_number')
                    ->where(fn ($q) => $q->where('company_id', $companyId))
                    ->ignore($driverId)
            ],
            'license_expiry_date' => ['sometimes', 'date'],
            'avatar' => ['sometimes', 'nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }
}
