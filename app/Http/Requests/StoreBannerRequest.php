<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBannerRequest extends FormRequest
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
        // Kiểm tra xem đây là request tạo mới hay cập nhật
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        
        return [
            'UserId'    => 'required|exists:users,UserId',
            'Title'     => 'required|string|max:255',
            // ImageUrl: bắt buộc khi tạo mới, nullable khi update
            'ImageUrl'  => $isUpdate 
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
                : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'LinkUrl'   => 'nullable|string|max:255',
            'Position'  => 'required|string|max:50',
            'Status'    => 'required|string|max:20',
        ];
    }
}