<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'StaffId'=>$this->StaffId,
            'FullName'=>$this->FullName,
            'Email'=>$this->Email,
            'Phone'=>$this->Phone,
            'Position'=>$this->Position,
            'CinemaId'=>new CinemaResource($this->when($this->cinema, $this->cinema)),
            'Status'=>$this->Status,
            'CreatedBy'=>$this->CreatedBy,
            'UpdatedBy'=>$this->UpdatedBy,
            'CreatedAt'=>$this->CreatedAt?->format('d-m-Y'),
            'UpdatedAt'=>$this->UpdatedAt?->format('d-m-Y'),
        ];
    }
}
