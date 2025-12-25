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
            'MovieId' => new MovieResource($this->whenLoaded('movie')),
            'Name' => $this->Name,
            'MovieId' => $this->MovieId,

            'Role' => $this->Role,
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),

        ];
    }
}
