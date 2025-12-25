<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCinemaRequest extends FormRequest
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
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        return [
            'Name'     => 'required|string|max:255',
            'Address'     => 'required|string|max:255',
            'City'  => 'required|string|max:255',
            'Phone'   => 'required|string|max:255',
            'Email'  => 'nullable|string|max:50',
            // ImageUrl: bắt buộc khi tạo mới, nullable khi update
            'ImageUrl'  => $isUpdate
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
                : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'Status'    => 'required|string|max:20',
        ];
    }
}
