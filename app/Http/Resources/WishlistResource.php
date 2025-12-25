<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WishlistResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'WishlistId' => $this->WishlistId,
            'UserId'     => $this->UserId,
            'MovieId'    => $this->MovieId,
            'Status'     => $this->Status,
            'CreatedAt'  => $this->CreatedAt?->format('Y-m-d H:i:s'),
            'UpdatedAt'  => $this->UpdatedAt?->format('Y-m-d H:i:s'),

            // Nếu muốn hiển thị thông tin liên quan luôn:
            'User'  => $this->whenLoaded('user', fn() => [
                'UserId'   => $this->user->UserId,
                'Username' => $this->user->Username,
            ]),
            'Movie' => $this->whenLoaded('movie', fn() => [
                'MovieId' => $this->movie->MovieId,
                'Title'   => $this->movie->Title,
            ]),
        ];
    }
}
