<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowtimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ShowtimeId' => $this->ShowtimeId,
            'MovieId' => new MovieResource($this->when($this->movie, $this->movie)),
            'RoomId' => new RoomResource($this->when($this->room, $this->room)),
            'StartTime' => $this->StartTime?->format('d-m-Y H:i:s'),
            'EndTime' => $this->EndTime?->format('d-m-Y H:i:s'),
            'Price' => $this->Price,
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y H:i:s'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y H:i:s'),

        ];
    }
}
