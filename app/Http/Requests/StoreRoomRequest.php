<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'CinemaId'   => 'required|integer|exists:cinemas,CinemaId',
            'Name'       => 'required|string|max:255',
            // SeatCount không cần validation vì sẽ tự động tính
            'RoomType'   => 'required|string|in:2D,3D,4DX,IMAX',
            'Status'     => 'required|string|in:Active,Inactive,Maintenance',
        ];
    }

   
}