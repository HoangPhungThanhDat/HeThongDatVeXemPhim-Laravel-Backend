<?php

// namespace App\Http\Resources;

// use Illuminate\Http\Request;
// use Illuminate\Http\Resources\Json\JsonResource;

// class CinemaResource extends JsonResource
// {
//     /**
//      * Transform the resource into an array.
//      *
//      * @return array<string, mixed>
//      */
//     public function toArray(Request $request): array
//     {
//         return 
//         [
//             'CinemaId' => $this->CinemaId,
//             'Name' => $this->Name,
//             'Address' => $this->Address,
//             'City' => $this->City,
//             'ImageUrl' => $this->ImageUrl,
//             'Phone' => $this->Phone,
//             'Email' => $this->Email,
//             'Status' => $this->Status,
//             'CreatedBy' => $this->CreatedBy,
//             'UpdatedBy' => $this->UpdatedBy,
//             'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
//             'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),

//         ];
//     }
// }























namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CinemaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // ✅ Lấy danh sách phòng của rạp
        $rooms = $this->rooms;
        $totalRooms = $rooms->count();
        $totalSeats = 0;
        $activeRooms = 0;
        
        // ✅ Tính tổng số ghế và số phòng hoạt động
        foreach ($rooms as $room) {
            // Lấy số ghế từ mỗi phòng
            $totalSeats += $room->seats->count() ?? 0;
            if ($room->Status === 'Active') {
                $activeRooms++;
            }
        }
        
        return [
            'CinemaId' => $this->CinemaId,
            'Name' => $this->Name,
            'Address' => $this->Address,
            'City' => $this->City,
            'ImageUrl' => $this->ImageUrl,
            'Phone' => $this->Phone,
            'Email' => $this->Email,
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),
            // ✅ Thêm các trường mới
            'TotalRooms' => $totalRooms,
            'ActiveRooms' => $activeRooms,
            'TotalSeats' => $totalSeats,
            'Rooms' => RoomResource::collection($rooms),
        ];
    }
}