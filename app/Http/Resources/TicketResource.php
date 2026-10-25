<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'TicketId' => $this->TicketId,
            'UserId' => $this->UserId,
            'BookingTime' => $this->BookingTime?->format('d-m-Y H:i:s'),
            'Status' => $this->Status,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y H:i:s'),
            
            // Trả về user
            'user' => $this->whenLoaded('user', function () {
                return [
                    'UserId' => $this->user->UserId,
                    'FullName' => $this->user->FullName,
                    'Email' => $this->user->Email,
                    'PhoneNumber' => $this->user->PhoneNumber,
                ];
            }),
            
            // Trả về seat
            'seat' => $this->whenLoaded('seat', function () {
                return [
                    'SeatId' => $this->seat->SeatId,
                    'Row' => $this->seat->Row,
                    'Number' => $this->seat->Number,
                    'SeatType' => $this->seat->SeatType,
                ];
            }),
            
            // ⭐ SỬA LẠI CẤU TRÚC SHOWTIME CHO GIỐNG HỆT SHOWTIMERESOURCE CŨ
            'showtime' => $this->whenLoaded('showtime', function () {
                return [
                    'ShowtimeId' => $this->showtime->ShowtimeId,
                    'StartTime' => $this->showtime->StartTime,
                    'EndTime' => $this->showtime->EndTime,
                    'Price' => $this->showtime->Price,
                    'Status' => $this->showtime->Status,
                    'movie' => $this->showtime->relationLoaded('movie') ? [
                        'MovieId' => $this->showtime->movie->MovieId,
                        'Title' => $this->showtime->movie->Title,
                        'PosterUrl' => $this->showtime->movie->PosterUrl,
                        'Duration' => $this->showtime->movie->Duration,
                    ] : null,
                    'room' => $this->showtime->relationLoaded('room') ? [
                        'RoomId' => $this->showtime->room->RoomId,
                        'Name' => $this->showtime->room->Name,
                        'CinemaName' => $this->showtime->room->cinema->CinemaName ?? null, // Nếu cần tên rạp
                    ] : null,
                ];
            }),

            // Trả về combo
            'orderDetails' => $this->whenLoaded('orderDetails', function () {
                return $this->orderDetails->map(function ($detail) {
                    return [
                        'OrderDetailId' => $detail->OrderDetailId,
                        'ItemId' => $detail->ItemId,
                        'Price' => $detail->Price,
                        'item' => $detail->relationLoaded('item') ? [
                            'Name' => $detail->item->Name,
                        ] : null,
                    ];
                });
            }),
        ];
    }
}