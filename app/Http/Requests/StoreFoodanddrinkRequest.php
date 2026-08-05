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
    public function rules(): array
    {
        // Kiểm tra xem đây là request tạo mới hay cập nhật
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        
        return [
            'Name' => 'required|string|max:255',
            'Description' => 'nullable|string',
            'Price' => 'required|numeric|min:0',
            'IsAvailable' => 'nullable|boolean',
            'Status' => 'nullable|in:Active,Inactive,OutOfStock',
            'Category' => 'nullable|string|max:100',
            'Items' => 'nullable|array',
            'Items.*' => 'string|max:255',
            
            // ✅ QUAN TRỌNG: Xử lý ImageUrl khác nhau cho create và update
            'ImageUrl' => $isUpdate
                // ✅ Khi UPDATE: ImageUrl có thể là string (URL cũ) hoặc file ảnh (nếu đổi ảnh)
                ? 'nullable|string'
                // ✅ Khi CREATE: ImageUrl phải là file ảnh (nếu có) hoặc null
                : 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'Name.required' => 'Tên sản phẩm là bắt buộc',
            'Name.string' => 'Tên sản phẩm phải là chuỗi ký tự',
            'Name.max' => 'Tên sản phẩm không được vượt quá 255 ký tự',
            
            'Price.required' => 'Giá sản phẩm là bắt buộc',
            'Price.numeric' => 'Giá sản phẩm phải là số',
            'Price.min' => 'Giá sản phẩm phải lớn hơn hoặc bằng 0',
            
            'ImageUrl.image' => 'File phải là ảnh',
            'ImageUrl.mimes' => 'Định dạng ảnh không hợp lệ (chấp nhận: jpeg, png, jpg, gif, webp)',
            'ImageUrl.max' => 'Kích thước ảnh tối đa 5MB',
            
            'Status.in' => 'Trạng thái không hợp lệ (Active, Inactive, OutOfStock)',
            
            'Items.array' => 'Thành phần combo phải là mảng',
            'Items.*.string' => 'Tên thành phần combo phải là chuỗi ký tự',
        ];
    }
}