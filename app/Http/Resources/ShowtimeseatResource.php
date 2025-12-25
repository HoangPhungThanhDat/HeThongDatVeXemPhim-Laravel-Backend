<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowtimeseatResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ShowtimeSeatId' => $this->ShowtimeSeatId,
            'ShowtimeId' => $this->ShowtimeId,
            'SeatId' => $this->SeatId,
            'Status' => $this->Status,
            'CreatedAt' => $this->CreatedAt ? $this->CreatedAt->format('Y-m-d H:i:s') : null,
            'UpdatedAt' => $this->UpdatedAt ? $this->UpdatedAt->format('Y-m-d H:i:s') : null,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'Showtime' => $this->showtime ? [
                'Name' => $this->showtime->Name ?? null,
                'StartTime' => $this->showtime->StartTime ?? null
            ] : null,
            'Seat' => $this->seat ? [
                'Row' => $this->seat->Row ?? null,
                'Number' => $this->seat->Number ?? null,
                'SeatType' => $this->seat->SeatType ?? null
            ] : null,
        ];
    }
}
