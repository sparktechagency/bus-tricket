<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdatePassengerProfile extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Admin is updating another user. Determine target user id from route or payload.
        $userId = $this->route('user')
            ?? $this->route('passenger')
            ?? $this->route('id')
            ?? $this->input('user_id');


        return [
            'name' => ['sometimes','required','string','max:255'],
            'email' => [
                'sometimes','required','email','max:255',
                Rule::unique('users','email')->ignore($userId),
            ],
            'phone_number' => [
                'sometimes','nullable','string','max:15',
                Rule::unique('users','phone_number')->ignore($userId),
            ],
            'address' => ['sometimes','nullable','string','max:255'],
            // Optional passenger-specific fields (include if updating)
            'rider_type' => ['sometimes','nullable','in:adult,child,student'],
            'status' => ['sometimes','nullable','in:active,pending,suspended'],
        ];
    }
}

