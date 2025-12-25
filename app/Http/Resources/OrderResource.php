<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'OrderId'      => $this->OrderId,
            'UserId'       => $this->UserId,
            'OrderDate'    => $this->OrderDate?->format('Y-m-d H:i:s'),
            'TotalAmount'  => $this->TotalAmount,
            'PromotionId'  => $this->PromotionId,
            'Status'        => $this->Status,
            'CreatedBy'    => $this->CreatedBy,
            'UpdatedBy'    => $this->UpdatedBy,
            'CreatedAt'    => $this->CreatedAt?->format('Y-m-d H:i:s'),
            'UpdatedAt'    => $this->UpdatedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
