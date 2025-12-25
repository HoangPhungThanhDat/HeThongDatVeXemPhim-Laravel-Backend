<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FoodanddrinkResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return 
        [
            'ItemId'=>$this->ItemId,
            'Name'=>$this->Name,
            'Description'=>$this->Description,
            'Price'=>$this->Price,
            'ImageUrl'=>$this->ImageUrl,
            "IsAvailable"=>$this->IsAvailable,
            'Status'=>$this->Status,
            'CreatedBy' => $this->CreatedBy,
            'UpdatedBy' => $this->UpdatedBy,
            'CreatedAt' => $this->CreatedAt?->format('d-m-Y'),
            'UpdatedAt' => $this->UpdatedAt?->format('d-m-Y'),



        ];
    }
}
