<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSeatRequest extends FormRequest
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
            'RoomId' => 'required|integer|exists:rooms,RoomId',
            'Row' => 'required|string|max:5', // ví dụ: Row có thể là "A", "B", "C"...
            'Number' => 'required|integer|min:1',
            'SeatType' => 'required|string|in:Normal,VIP', // nếu chỉ có 2 loại ghế
            'Status' => 'required|string|in:Available,Reserved,Occupied', // trạng thái hợp lệ
        ];
    }
}
