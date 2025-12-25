<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
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
            'NotificationId' => $this->NotificationId,
            'UserId' => $this->UserId,
            'Title' => $this->Title,
            'Message' => $this->Message,
            'Type' => $this->Type,
            'IsRead' => $this->IsRead,
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),

        ];
    }
}
