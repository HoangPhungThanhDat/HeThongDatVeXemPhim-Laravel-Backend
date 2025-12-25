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
            'UserId' => $this->UserId,
            'FullName' => $this->FullName,
            'Email' => $this->Email,
            'PhoneNumber' => $this->PhoneNumber,
            'Gender' => $this->Gender,
            'DateOfBirth' => $this->DateOfBirth?->format('d-m-Y'),
            'role' => new RoleResource($this->whenLoaded('role')),
            'role' => $this->Role,
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),
        ];
    }
}
