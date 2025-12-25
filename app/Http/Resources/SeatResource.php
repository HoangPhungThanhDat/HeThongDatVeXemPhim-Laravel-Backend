<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'SeatId'        => $this->SeatId,
            'RoomId'      => $this->RoomId,
            'Row'          => $this->Row,
            'Number'       => $this->Number,
            'SeatType'      => $this->SeatType,
            'Status'    => $this->Status,
            'CreatedAt'           => $this->CreatedAt?->format('d-m-Y H:i:s'),
            'UpdatedAt'      => $this->UpdatedAt?->format('d-m-Y H:i:s'),
            'CreatedBy'     => $this->CreatedBy,
            'UpdatedBy'=> $this->UpdatedBy,

            // thông tin phòng
            'Phong' => $this->whenLoaded('room', function() {
                return [
                    'RoomId' => $this->room->RoomId,
                    'Name'=> $this->room->Name ?? null,
                ];
            }),

            // thông tin showtime liên quan
            'Lich_Chieu' => ShowtimeSeatResource::collection($this->whenLoaded('showtimes')),
        ];
    }
}
