<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'CinemaId'   => 'required|integer|exists:cinemas,CinemaId',
            'Name'       => 'required|string|max:255',
            'SeatCount'  => 'required|integer|min:1',
            'RoomType'   => 'required|string|in:2D,3D,4DX,IMAX',
            'Status'     => 'required|string|in:Active,Inactive,Maintenance',
        ];
    }
}
