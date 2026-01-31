<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFoodanddrinkRequest extends FormRequest
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
    //kiểm tra dữ liệu đầu vào
    public function rules(): array
    {
        // Kiểm tra xem đây là request tạo mới hay cập nhật
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        return [

            'Name'        => 'required|string|max:255',
            'Description' => 'nullable|string',
            'Price'       => 'required|numeric|min:0',
            // ImageUrl: bắt buộc khi tạo mới, nullable khi update
            'ImageUrl'  => $isUpdate
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
                : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'IsAvailable' => 'required|boolean',
            'Status' => 'required|in:Active,Inactive,OutOfStock',
        ];
    }
}
