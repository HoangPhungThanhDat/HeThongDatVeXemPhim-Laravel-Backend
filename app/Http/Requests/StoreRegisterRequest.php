<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;

class StoreRegisterRequest extends FormRequest
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
            'FullName'    => 'required|string|max:255',
            'Email'       => 'required|email|unique:users,Email',
            'Password'    => 'required|string|min:8',
            'PhoneNumber' => 'nullable|string|max:20',
            'Gender'      => 'required|in:Male,Female,Other',
            'DateOfBirth' => 'nullable|date',
            'RoleId'      => 'required|integer|exists:roles,RoleId',
            'Status'      => 'nullable|in:Active,Inactive,Banned',
        ];
    }
}
