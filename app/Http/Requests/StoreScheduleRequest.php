<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreScheduleRequest extends FormRequest
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
            'MovieId'    => 'required|integer|exists:movies,MovieId',
            'RoomId'     => 'required|integer|exists:rooms,RoomId',

            'StartDate'  => 'required|date',
            'EndDate'    => 'required|date|after_or_equal:StartDate',

            'DaysOfWeek' => 'required|array',
            'DaysOfWeek.*' => 'in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',

            'StartTime'  => 'required|date_format:H:i',
            'EndTime'    => 'required|date_format:H:i|after:StartTime',

            'Price'      => 'required|numeric|min:0',

            'Status'     => 'required|in:Active,Inactive',
        ];
    }
}
