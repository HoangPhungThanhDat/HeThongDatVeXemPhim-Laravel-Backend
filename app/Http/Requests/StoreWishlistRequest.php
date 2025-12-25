<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWishlistRequest extends FormRequest
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
            // UserId: bắt buộc, kiểu số nguyên, tồn tại trong bảng users
            'UserId'   => 'required|integer|exists:users,UserId',

            // MovieId: bắt buộc, kiểu số nguyên, tồn tại trong bảng movies
            'MovieId'  => 'required|integer|exists:movies,MovieId',

            // Status: chỉ được nhận 2 giá trị
            'Status'   => 'required|in:Active,Inactive',
        ];
    }
}
