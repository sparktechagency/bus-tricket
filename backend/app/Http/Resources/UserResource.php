<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->company_id,
            'name' => $this->name,
            'username' => $this->username,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'phone_number' => $this->phone_number,
            'avatar' => $this->avatar,
            'address' => $this->address,
            'rider_type' => $this->when(!is_null($this->rider_type), $this->rider_type),
            'qr_code_number' => $this->when(!is_null($this->qr_code_number), $this->qr_code_number),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'roles' => RoleResource::collection($this->whenLoaded('roles')),

            // Conditionally include permissions only if 'roles' relationship is loaded
            $this->mergeWhen($this->relationLoaded('roles'), [
                'permissions' => $this->getAllPermissions()->pluck('name'),
            ]),
        ];
    }
}
