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
            // ✅ Thông tin ghế
            'SeatId' => $this->SeatId,
            'RoomId' => $this->RoomId,
            'Row' => $this->Row,
            'Number' => $this->Number,
            'SeatType' => $this->SeatType,
            'Status' => $this->Status,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y H:i:s'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y H:i:s'),
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,

            // ✅ Thông tin phòng (nếu đã load relationship)
            'Room' => $this->whenLoaded('room', function() {
                return [
                    'RoomId' => $this->room->RoomId,
                    'Name' => $this->room->Name ?? null,
                    'RoomType' => $this->room->RoomType ?? null,
                    'Status' => $this->room->Status ?? null,
                ];
            }),

            // ✅ Thông tin rạp (qua phòng)
            'Cinema' => $this->whenLoaded('room.cinema', function() {
                return [
                    'CinemaId' => $this->room->cinema->CinemaId ?? null,
                    'Name' => $this->room->cinema->Name ?? null,
                ];
            }),

            // ✅ Thông tin ShowtimeSeat (trạng thái ghế theo suất chiếu)
            'ShowtimeSeats' => ShowtimeSeatResource::collection(
                $this->whenLoaded('showtimeSeats')
            ),
        ];
    }
}