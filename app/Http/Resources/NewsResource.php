<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'NewsId'     => $this->NewsId,
            'UserId'     => $this->UserId,
            'Title'        => $this->Title,
            'Slug'  => $this->Slug,
            'Content'       => $this->Content,
            'ImageUrl'       => $this->ImageUrl,
            'Status'     => $this->Status,
            'CreatedBy'      => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt'       => $this->CreatedAt?->format('d-m-Y H:i:s'),
            'UpdatedAt'  => $this->UpdatedAt?->format('d-m-Y H:i:s'),
        ];
    }
}
