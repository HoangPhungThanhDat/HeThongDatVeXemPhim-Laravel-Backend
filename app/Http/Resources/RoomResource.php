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
        // ✅ Lấy số ghế thực tế của phòng
        $actualSeats = $this->seats->count() ?? 0;
        
        return [
            'RoomId' => $this->RoomId,
            'CinemaId' => $this->CinemaId,
            'Name' => $this->Name,
            'SeatCount' => $this->SeatCount,
            'ActualSeats' => $actualSeats, // ✅ Số ghế thực tế (từ bảng seats)
            'RoomType' => $this->RoomType,
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y H:i:s'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y H:i:s'),
            // ✅ Thêm thông tin ghế chi tiết (nếu cần)
            'Seats' => SeatResource::collection($this->seats),
        ];
    }
}