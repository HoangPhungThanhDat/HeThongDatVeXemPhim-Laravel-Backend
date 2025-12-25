<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactRequest extends FormRequest
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
            'UserId'    => 'required|exists:users,UserId',
            'FullName'     => 'required|string|max:255',
            'Email'  => 'nullable|string|max:255',
            'Phone'   => 'nullable|string|max:11',
            'Message'  => 'required|string|max:100',
            'Status'    => 'required|string|max:20',
        ];
    }
}
