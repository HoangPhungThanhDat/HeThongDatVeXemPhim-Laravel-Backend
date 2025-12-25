<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MovieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'MovieId' => $this->MovieId,
            'Title' => $this->Title,
            'Slug' => $this->Slug,
            'Description' => $this->Description,
            'GenreId' => $this->GenreId,
            'Duration' => $this->Duration,
            'ReleaseDate' => $this->ReleaseDate?->format('d-m-Y'),
            'PosterUrl' => $this->PosterUrl,
            'TrailerUrl' => $this->TrailerUrl,
            'Language' => $this->Language,
            'Rated' => $this->Rated, 
            'Status' => $this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),

        ];
    }
}
