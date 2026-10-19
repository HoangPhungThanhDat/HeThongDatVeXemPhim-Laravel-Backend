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
        // Lưu ý: với method spoofing (_method=PUT + POST), isMethod('PUT') vẫn trả về true
        // vì Laravel đã spoof method trước khi vào FormRequest.
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');

        return [
            // UserId: bắt buộc khi tạo mới, không bắt buộc khi update
            'UserId'    => $isUpdate
                ? 'sometimes|required|exists:users,UserId'
                : 'required|exists:users,UserId',

            // Title: bắt buộc khi tạo mới, chỉ validate khi có mặt lúc update
            'Title'     => $isUpdate
                ? 'sometimes|required|string|max:255'
                : 'required|string|max:255',

            // ImageUrl: bắt buộc file ảnh khi tạo mới, nullable khi update
            'ImageUrl'  => $isUpdate
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
                : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            // LinkUrl: không bắt buộc
            'LinkUrl'   => 'nullable|string|max:255',

            // Position: bắt buộc khi tạo mới, chỉ validate khi có mặt lúc update
            'Position'  => $isUpdate
                ? 'sometimes|required|string|max:50'
                : 'required|string|max:50',

            // Status: bắt buộc khi tạo mới, chỉ validate khi có mặt lúc update
            'Status'    => $isUpdate
                ? 'sometimes|required|string|max:20'
                : 'required|string|max:20',
        ];
    }

    /**
     * Thông báo lỗi tùy chỉnh (tiếng Việt).
     */
    public function messages(): array
    {
        return [
            'UserId.required'   => 'Người dùng không được để trống.',
            'UserId.exists'     => 'Người dùng không tồn tại.',
            'Title.required'    => 'Tiêu đề banner không được để trống.',
            'Title.max'         => 'Tiêu đề banner không vượt quá 255 ký tự.',
            'ImageUrl.required' => 'Vui lòng chọn ảnh banner.',
            'ImageUrl.image'    => 'File tải lên phải là ảnh.',
            'ImageUrl.mimes'    => 'Ảnh phải có định dạng: jpeg, png, jpg, gif, webp.',
            'ImageUrl.max'      => 'Ảnh không được vượt quá 2MB.',
            'LinkUrl.max'       => 'Đường dẫn liên kết không vượt quá 255 ký tự.',
            'Position.required' => 'Vị trí hiển thị không được để trống.',
            'Position.max'      => 'Vị trí không vượt quá 50 ký tự.',
            'Status.required'   => 'Trạng thái không được để trống.',
            'Status.max'        => 'Trạng thái không vượt quá 20 ký tự.',
        ];
    }
}