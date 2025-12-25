<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
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
            'CinemaId'       => 'required|exists:cinemas,CinemaId',
            'FullName'       => 'required|string|max:255',
            'Email'          => 'required|email|max:255|unique:staff,Email,' . $this->route('StaffId') . ',StaffId',
            'Phone'          => 'required|string|max:20',
            'Position'       => 'required|string|max:100',
            'Status'         => 'required|in:Active,Inactive',
        ];
    }
}
