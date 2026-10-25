<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowtimeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'ShowtimeId' => $this->ShowtimeId,
            'MovieId' => $this->MovieId, // ✅ Trả về ID gốc
            'RoomId' => $this->RoomId,   // ✅ Trả về ID gốc
            
            // ✅ Trả về chuẩn YYYY-MM-DD HH:mm:ss để Frontend parse chuẩn xác
            'StartTime' => $this->StartTime ? $this->StartTime->format('Y-m-d H:i:s') : null,
            'EndTime' => $this->EndTime ? $this->EndTime->format('Y-m-d H:i:s') : null,
            
            'Price' => $this->Price,
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt ? $this->CreatedAt->format('Y-m-d H:i:s') : null,
            'UpdatedAt' => $this->UpdatedAt ? $this->UpdatedAt->format('Y-m-d H:i:s') : null,

            // ✅ Nếu bạn muốn lấy luôn tên phim/phòng thì thêm quan hệ vào đây
            'Movie' => new MovieResource($this->whenLoaded('movie')),
            'Room' => new RoomResource($this->whenLoaded('room')),
        ];
    }
}