<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShowtimeseatRequest extends FormRequest
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
            'ShowtimeId' => 'required|integer|exists:showtimes,ShowtimeId',
            'SeatId' => 'required|integer|exists:seats,SeatId',
            'Status' => 'required|string|in:Available,Reserved,Broken',
        ];
    }
}
