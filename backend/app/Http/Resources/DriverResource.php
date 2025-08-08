<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DriverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'user_id' => $this->user_id,
            'staff_number' => $this->staff_number,
            
            // 'pin_code' => $this->pin_code,
            'license_number' => $this->license_number,
            'license_expiry_date' => $this->license_expiry_date,
            'date_of_birth' => $this->date_of_birth,
            'rating' => $this->rating,
            'experience_years' => $this->experience_years,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // This line loads the user relationship and transforms it using UserResource
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
