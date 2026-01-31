<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkStoreSeatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'seats' => 'required|array|min:1',
            'seats.*.RoomId' => 'required|integer|exists:rooms,RoomId',
            'seats.*.Row' => 'required|string|max:5',
            'seats.*.Number' => 'required|integer|min:1',
            'seats.*.SeatType' => 'required|string|in:Normal,VIP,Couple',
            'seats.*.Status' => 'nullable|string|in:Available,Broken,Inactive,Reserved,Occupied',
        ];
    }

 
}