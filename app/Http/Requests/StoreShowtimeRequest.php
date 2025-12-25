<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShowtimeRequest extends FormRequest
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
            'MovieId'       => 'required|exists:movies,MovieId',
            'RoomId'        => 'required|exists:rooms,RoomId',
            'StartTime'     => 'required|date',
            'EndTime'       => 'required|date|after:StartTime',
            'Price'         => 'required|numeric|min:0',
            'Status'        => 'required|in:Scheduled,Cancelled,Finished',
            
        ];
    }
}
