<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MoviecastResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'CastId' => $this->CastId,
            'MovieId' => $this->MovieId,
            'Name' => $this->Name,
            'Role' => $this->Role,
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('Y-m-d H:i:s'),
            'UpdatedAt' => $this->UpdatedAt?->format('Y-m-d H:i:s'),
            
            // ✅ THÊM: Role name tiếng Việt
            'RoleName' => $this->role_name,
            'IsDirector' => $this->is_director,
            'IsActor' => $this->is_actor,
        ];
    }
}