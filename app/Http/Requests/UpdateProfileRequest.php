<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = Auth::guard('api')->user()?->UserId;   // ✅ FIX

        return [
            'FullName' => 'required|string|min:2|max:255',

            'Email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'Email')->ignore($userId, 'UserId'),
            ],

            'PhoneNumber' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/',
                Rule::unique('users', 'PhoneNumber')->ignore($userId, 'UserId'),
            ],

            'Address' => 'required|string|min:5|max:500',
            'DateOfBirth' => 'nullable|date|before:today',
            'Gender' => 'nullable|in:Male,Female',
        ];
    }

    public function messages(): array
    {
        return [
            'FullName.required' => 'Vui lòng nhập họ tên',
            'FullName.min' => 'Họ tên phải có ít nhất 2 ký tự',
            'FullName.max' => 'Họ tên không được vượt quá 255 ký tự',

            'Email.required' => 'Vui lòng nhập email',
            'Email.email' => 'Email không hợp lệ',
            'Email.max' => 'Email không được vượt quá 255 ký tự',
            'Email.unique' => 'Email đã được sử dụng',

            'PhoneNumber.required' => 'Vui lòng nhập số điện thoại',
            'PhoneNumber.regex' => 'Số điện thoại phải có đúng 10 chữ số',
            'PhoneNumber.unique' => 'Số điện thoại đã được sử dụng',

            'Address.required' => 'Vui lòng nhập địa chỉ',
            'Address.min' => 'Địa chỉ phải có ít nhất 5 ký tự',
            'Address.max' => 'Địa chỉ không được vượt quá 500 ký tự',

            'DateOfBirth.date' => 'Ngày sinh không hợp lệ',
            'DateOfBirth.before' => 'Ngày sinh phải trước ngày hôm nay',

            'Gender.in' => 'Giới tính phải là Male hoặc Female',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'FullName'    => is_string($this->FullName) ? trim($this->FullName) : $this->FullName,
            'Email'       => is_string($this->Email) ? strtolower(trim($this->Email)) : $this->Email,
            'PhoneNumber' => is_string($this->PhoneNumber) ? trim($this->PhoneNumber) : $this->PhoneNumber,
            'Address'     => is_string($this->Address) ? trim($this->Address) : $this->Address,
        ]);
    }
}