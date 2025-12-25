<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'ReviewId' => $this->ReviewId,
            'User' => new UserResource($this->when($this->user, $this->user)),
            'MovieId' => new MovieResource($this->when($this->movie, $this->movie)),
            'Rating' => $this->Rating,
            'UserId' => $this->UserId,

            'Comment' => $this->Comment,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'Status' => $this->Status,
        ];
    }
}
