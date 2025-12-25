<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'TicketId' => $this->TicketId,
            'ShowtimeId' => new ShowtimeResource($this->when($this->showtime, $this->showtime)),
            'SeatId'  => new SeatResource($this->when($this->seat, $this->seat)),
            'user' => new UserResource($this->whenLoaded('user')),
            'UserId' => $this->UserId,
            'BookingTime' => $this->BookingTime?->format('d-m-Y H:i:s'),
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y H:i:s'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y H:i:s'),
        ];
    }
}
