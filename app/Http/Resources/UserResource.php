<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'profile_picture' => $this->when($this->profile_picture, asset('storage/' . $this->profile_picture)),
            'detail' => [
                'phone_number' => $this->userDetail->phone_number,
                'insurance_type' => $this->userDetail->insurance_type,
                'insurance_number' => $this->userDetail->insurance_number,
                'plz' => $this->userDetail->plz,
            ],
        ];
    }
}
