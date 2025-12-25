<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'PromotionId' => $this->PromotionId,
            'Title' => $this->Title,
            'Code' => $this->Code,
            'Description' => $this->Description,
            'ImageUrl' => $this->ImageUrl,
            'DiscountType' => $this->DiscountType,
            'DiscountValue' => $this->DiscountValue,
            'StartDate' => $this->StartDate?->format('d-m-Y'),
            'EndDate' => $this->EndDate?->format('d-m-Y'),
            'IsActive' => $this->IsActive,
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),

        ];

    }
}
