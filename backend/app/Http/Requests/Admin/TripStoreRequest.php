<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;

class TripStoreRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'route_id' => ['required', 'integer', 'exists:routes,id'],
            'departure_time' => ['required', 'date_format:H:i'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}

