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
            'route_prefix' => ['nullable', 'string', 'max:10'],
            'google_map_link' => ['nullable', 'url'],
            'status' => ['required', 'boolean'],

            'stops' => ['required', 'array', 'min:2'],
            'stops.*.location_name' => ['required', 'string', 'max:255'],
            'stops.*.stop_order' => ['required', 'integer'],
            'stops.*.minutes_from_start' => ['required', 'integer', 'min:0'],
            'stops.*.latitude' => ['required', 'numeric', 'between:-90,90'],
            'stops.*.longitude' => ['required', 'numeric', 'between:-180,180'],

            'fares' => ['required', 'array'],
            'fares.*.passenger_type' => ['required', 'string'],
            'fares.*.cash_amount' => ['required', 'numeric', 'min:0'],
            'fares.*.app_amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
