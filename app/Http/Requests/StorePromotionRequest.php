<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            'Title'         => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'Code'          => $isUpdate ? 'sometimes|required|string|max:100' : 'required|string|max:100',
            'Description'   => 'nullable|string',

            'ImageUrl'      => $isUpdate
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
                : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            'DiscountType'  => $isUpdate ? 'sometimes|required|in:Percentage,FixedAmount' : 'required|in:Percentage,FixedAmount',
            'DiscountValue' => $isUpdate ? 'sometimes|required|numeric|min:0' : 'required|numeric|min:0',
            'StartDate'     => $isUpdate ? 'sometimes|required|date' : 'required|date',
            'EndDate'       => $isUpdate ? 'sometimes|required|date|after_or_equal:StartDate' : 'required|date|after_or_equal:StartDate',

            'IsActive'      => $isUpdate ? 'sometimes|required|boolean' : 'required|boolean',

            // ✅ Cách 2: DB chỉ có Active/Inactive/Expired
            'Status'        => $isUpdate
                ? 'sometimes|required|in:Active,Inactive,Expired,Paused'
                : 'required|in:Active,Inactive,Expired',
        ];
    }

    public function messages(): array
    {
        return [
            'Title.required'         => 'Tiêu đề khuyến mãi không được để trống.',
            'Code.required'          => 'Mã khuyến mãi không được để trống.',
            'DiscountType.required'  => 'Loại giảm giá không được để trống.',
            'DiscountType.in'        => 'Loại giảm giá phải là Percentage hoặc FixedAmount.',
            'DiscountValue.required' => 'Giá trị giảm giá không được để trống.',
            'DiscountValue.numeric'  => 'Giá trị giảm giá phải là số.',
            'StartDate.required'     => 'Ngày bắt đầu không được để trống.',
            'EndDate.required'       => 'Ngày kết thúc không được để trống.',
            'EndDate.after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
            'ImageUrl.required'      => 'Vui lòng chọn ảnh khuyến mãi.',
            'ImageUrl.image'         => 'File tải lên phải là ảnh.',
            'ImageUrl.max'           => 'Ảnh không được vượt quá 2MB.',
            'IsActive.required'      => 'Trạng thái hoạt động không được để trống.',
            'IsActive.boolean'       => 'Trạng thái hoạt động phải là true/false.',
            'Status.required'        => 'Trạng thái không được để trống.',
            'Status.in'              => 'Trạng thái không hợp lệ.',
        ];
    }
}