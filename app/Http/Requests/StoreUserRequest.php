<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
            'FullName'     => 'required|string|max:255',
            'Email'        => 'required|email|max:255|unique:users,Email,' . $this->route('UserId') . ',UserId',
            'PasswordHash' => 'required|string|min:8',
            'PhoneNumber'  => 'nullable|string|max:20',
            'Gender'       => 'required|in:Male,Female,Other',
            'DateOfBirth'  => 'nullable|date',
            'RoleId'       => 'required|exists:roles,RoleId',
            'Status'       => 'required|in:Active,Inactive,Banned',
        ];
    }
}
