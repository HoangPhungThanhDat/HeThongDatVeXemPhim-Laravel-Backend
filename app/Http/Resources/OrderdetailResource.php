<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderdetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'OrderDetailId' => $this->OrderDetailId,
           'OrderId' => new OrderResource($this->when($this->order, $this->order)),
            'TicketId' => new TicketResource($this->when($this->ticket, $this->ticket)), 
            'ItemId' => new FoodanddrinkResource($this->when($this->foodanddrink, $this->foodanddrink)),
            'Quantity' => $this->Quantity,
            'Price' => $this->Price,
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),
        ];
    }
}
