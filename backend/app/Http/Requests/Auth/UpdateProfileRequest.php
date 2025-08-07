<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends BaseRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $userId = $this->user()->id;

        return [
            'name' => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|nullable|string|max:15|unique:users,phone_number,' . $userId,
            'avatar' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // 2MB max
            'address' => 'sometimes|nullable|string|max:255',
            'username' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('users')->ignore($userId),
            ],
        ];
    }
}
