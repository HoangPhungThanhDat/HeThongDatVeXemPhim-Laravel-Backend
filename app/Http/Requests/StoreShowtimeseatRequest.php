<?php

// namespace App\Http\Requests;

// use Illuminate\Foundation\Http\FormRequest;

// class StoreShowtimeseatRequest extends FormRequest
// {
//     /**
//      * Determine if the user is authorized to make this request.
//      */
//     public function authorize(): bool
//     {
//         return true;
//     }

//     /**
//      * Get the validation rules that apply to the request.
//      *
//      * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
//      */
//     public function rules(): array
//     {
//         return [
//             'ShowtimeId' => 'required|integer|exists:showtimes,ShowtimeId',
//             'SeatId' => 'required|integer|exists:seats,SeatId',
//             'Status' => 'required|string|in:Available,Reserved,Held,Broken,Maintenance,Inactive',
//         ];
//     }
// }

























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
        $method = $this->method();
        
        // ✅ Nếu là PUT/PATCH (update), các trường không bắt buộc
        if ($method === 'PUT' || $method === 'PATCH') {
            return [
                'ShowtimeId' => 'sometimes|integer|exists:showtimes,ShowtimeId',
                'SeatId' => 'sometimes|integer|exists:seats,SeatId',
                'Status' => 'sometimes|string|in:Available,Reserved,Held,Broken,Maintenance,Inactive',
            ];
        }
        
        // ✅ Nếu là POST (create), yêu cầu đầy đủ
        return [
            'ShowtimeId' => 'required|integer|exists:showtimes,ShowtimeId',
            'SeatId' => 'required|integer|exists:seats,SeatId',
            'Status' => 'required|string|in:Available,Reserved,Held,Broken,Maintenance,Inactive',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ShowtimeId.required' => 'ID suất chiếu là bắt buộc',
            'ShowtimeId.integer' => 'ID suất chiếu phải là số nguyên',
            'ShowtimeId.exists' => 'Suất chiếu không tồn tại trong hệ thống',
            
            'SeatId.required' => 'ID ghế là bắt buộc',
            'SeatId.integer' => 'ID ghế phải là số nguyên',
            'SeatId.exists' => 'Ghế không tồn tại trong hệ thống',
            
            'Status.required' => 'Trạng thái là bắt buộc',
            'Status.in' => 'Trạng thái không hợp lệ, chỉ chấp nhận: Available, Reserved, Held, Broken, Maintenance, Inactive',
        ];
    }
}