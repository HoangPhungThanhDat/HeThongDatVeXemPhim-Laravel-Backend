<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'RoomId' => 'required|integer|exists:rooms,RoomId',
            'Row' => 'required|string|max:5',
            'Number' => 'required|integer|min:1',
            'SeatType' => 'required|string|in:Normal,VIP,Couple',
            'Status' => 'nullable|string|in:Available,Broken,Inactive,Reserved,Occupied',
        ];
    }

   
}