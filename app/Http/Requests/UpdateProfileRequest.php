<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'FullName' => 'required|string|min:2|max:255',
            'Email' => 'required|email',
            'PhoneNumber' => 'required|string|regex:/^[0-9]{10}$/',
            'Address' => 'required|string|min:5|max:500',
            'DateOfBirth' => 'nullable|date|before:today',
            'Gender' => 'nullable|in:Male,Female',
        ];
    }

    public function messages()
    {
        return [
            'FullName.required' => 'Vui lòng nhập họ tên',
            'FullName.min' => 'Họ tên phải có ít nhất 2 ký tự',
            'Email.required' => 'Vui lòng nhập email',
            'Email.email' => 'Email không hợp lệ',
            'PhoneNumber.required' => 'Vui lòng nhập số điện thoại',
            'PhoneNumber.regex' => 'Số điện thoại phải có đúng 10 chữ số',
            'Address.required' => 'Vui lòng nhập địa chỉ',
            'Address.min' => 'Địa chỉ phải có ít nhất 5 ký tự',
            'DateOfBirth.date' => 'Ngày sinh không hợp lệ',
            'DateOfBirth.before' => 'Ngày sinh phải trước ngày hôm nay',
            'Gender.in' => 'Giới tính phải là Male hoặc Female',
        ];
    }
}