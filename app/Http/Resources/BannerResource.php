<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BannerResource extends JsonResource
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
                'BannerId' => $this->BannerId,
                'UserId'    => $this->UserId,
                'User'      => new UserResource($this->whenLoaded('user')),
                'Title' => $this->Title,
                'ImageUrl' => $this->ImageUrl,
                'LinkUrl' => $this->LinkUrl,
                'Position' => $this->Position,
                'Status' => $this->Status,
                'CreatedBy' => $this->CreatedBy,
                'UpdatedBy' => $this->UpdatedBy,
                'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
                'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),

            ];
    }
}
