<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'RoomId'     => $this->RoomId,
            'CinemaId'             => $this->CinemaId,
            'Name'          => $this->Name,
            'SeatCount'        => $this->SeatCount,
            'RoomType'   => $this->RoomType,
            'Status'         => $this->Status,
            'CreatedBy'          => $this->CreatedBy,
            'UpdatedBy'     => $this->UpdatedBy,
            'CreatedAt'           => $this->CreatedAt?->format('d-m-Y H:i:s'),
            'UpdatedAt'      => $this->UpdatedAt?->format('d-m-Y H:i:s'),
        ];
    }
}
