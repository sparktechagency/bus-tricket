<?php
// app/Http/Requests/Admin/RouteStoreRequest.php
namespace App\Http\Requests\Admin;

use App\Http\Requests\BaseRequest;

class RouteStoreRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'trip' => ['nullable', 'string', 'max:10'],
            'google_map_link' => ['nullable', 'url'],
            'status' => ['required', 'boolean'],

            'time_points' => ['required', 'array'],
            'time_points.*.location_name' => ['required', 'string'],
            'time_points.*.departure_time' => ['required', 'date_format:H:i'],

            'fares' => ['required', 'array'],
            'fares.*.passenger_type' => ['required', 'string'],
            'fares.*.cash_amount' => ['required', 'numeric', 'min:0'],
            'fares.*.app_amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
