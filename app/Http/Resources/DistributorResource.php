<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistributorResource extends JsonResource
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
           'DistributorId' => $this->DistributorId,
            'MovieId' => $this->MovieId,
            'Name' => $this->Name,
            'Country' => $this->Country,
            'Email' => $this->Email,
            'Phone' => $this->Phone,
            'Website' => $this->Website,
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),
        ];
    }
}
