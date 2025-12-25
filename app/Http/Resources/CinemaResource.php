<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CinemaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return 
        [
            'CinemaId' => $this->CinemaId,
            'Name' => $this->Name,
            'Address' => $this->Address,
            'City' => $this->City,
            'ImageUrl' => $this->ImageUrl,
            'Phone' => $this->Phone,
            'Email' => $this->Email,
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),

        ];
    }
}