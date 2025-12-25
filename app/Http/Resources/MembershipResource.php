<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembershipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'MembershipId' => $this->MembershipId,
            'UserId' => new UserResource($this->whenLoaded('user')),
            'Level' => $this->Level,
            'UserId' => $this->UserId,
            'Points' => $this->Points,
            'StartDate' => $this->StartDate?->format('d-m-Y'),
            'EndDate' => $this->EndDate?->format('d-m-Y'),
            'Benefits' => $this->Benefits,
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),
          
        ];

    }
}
