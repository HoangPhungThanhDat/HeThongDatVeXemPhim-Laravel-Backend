<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'PaymentId'      => $this->PaymentId,
            'TicketId'              => $this->TicketId,
            'Amount'            => $this->Amount,
            'PaymentMethod'        => $this->PaymentMethod,
            'PaymentStatus' => $this->PaymentStatus,
            'PaymentDate'    => $this->PaymentDate ? $this->PaymentDate->format('d/m/Y H:i:s') : null,
            'Status'         => $this->Status,
            'CreatedAt'           => $this->CreatedAt ? $this->CreatedAt->format('d/m/Y H:i:s') : null,
            'UpdatedAt'      => $this->UpdatedAt ? $this->UpdatedAt->format('d/m/Y H:i:s') : null,
            'CreatedBy'          => $this->CreatedBy,
            'UpdatedBy'     => $this->UpdatedBy,

            // Quan hệ
            // 'Thông tin vé'       => new TicketResource($this->whenLoaded('ticket')),
            // 'Người cập nhật bởi' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
