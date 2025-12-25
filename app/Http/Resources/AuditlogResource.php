<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditlogResource extends JsonResource
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
            'LogId' => $this->LogId,
            'UserId' => $this->UserId,
            'Action' => $this->Action,
            'Description' => $this->Description,
            'IpAddress' => $this->IpAddress,
            'DeviceInfo' => $this->DeviceInfo,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),

        ];

        
    }
}