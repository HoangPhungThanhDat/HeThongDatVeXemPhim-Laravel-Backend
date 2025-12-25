<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ScheduleId' => $this->ScheduleId,
            'MovieId'    => $this->MovieId,
            'RoomId'     => $this->RoomId,

            'StartDate'  => $this->StartDate?->format('d-m-Y'),
            'EndDate'    => $this->EndDate?->format('d-m-Y'),

            'DaysOfWeek' => $this->DaysOfWeek,

            'StartTime'  => $this->StartTime ? date('H:i', strtotime($this->StartTime)) : null,
            'EndTime'    => $this->EndTime ? date('H:i', strtotime($this->EndTime)) : null,


            'Price' => $this->Price !== null
                ? number_format((float)$this->Price, 0, ',', '.') . ' VNĐ'
                : '0 VNĐ',

            'Status'     => $this->Status,

            'CreatedAt'  => $this->CreatedAt?->format('Y-m-d H:i:s'),
            'UpdatedAt'  => $this->UpdatedAt?->format('Y-m-d H:i:s'),

            'CreatedBy'  => $this->CreatedBy,
            'UpdatedBy'  => $this->UpdatedBy,

            // Nếu muốn trả thêm thông tin quan hệ (movie, room) thì có thể làm như sau:
            'movie' => new MovieResource($this->whenLoaded('movie')),
            'room'  => new RoomResource($this->whenLoaded('room')),
        ];
    }
}
