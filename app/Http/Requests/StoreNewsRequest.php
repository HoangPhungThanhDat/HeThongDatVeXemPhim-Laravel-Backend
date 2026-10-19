<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNewsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT') || $this->isMethod('PATCH');
        // Lấy ID bài viết từ route (hỗ trợ nhiều tên param)
        $newsId = $this->route('NewsId')
            ?? $this->route('news')
            ?? $this->route('id');

        return [
            'UserId'  => $isUpdate
                ? 'sometimes|required|exists:users,UserId'
                : 'required|exists:users,UserId',

            'Title'   => $isUpdate
                ? 'sometimes|required|string|max:255'
                : 'required|string|max:255',

            // ✅ unique bỏ qua chính bản ghi đang update
            'Slug'    => $isUpdate
                ? ['nullable', 'string', Rule::unique('news', 'Slug')->ignore($newsId, 'NewsId')]
                : 'nullable|string|unique:news,Slug',

            'Content' => $isUpdate
                ? 'sometimes|required|string'
                : 'required|string',

            'ImageUrl' => $isUpdate
                ? 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
                : 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',

            // ✅ DB enum: Draft, Published, Hidden
            'Status'  => $isUpdate
                ? 'sometimes|required|in:Draft,Published,Hidden'
                : 'required|in:Draft,Published,Hidden',
        ];
    }

    public function messages(): array
    {
        return [
            'UserId.required'   => 'Người dùng không được để trống.',
            'UserId.exists'     => 'Người dùng không tồn tại.',
            'Title.required'    => 'Tiêu đề bài viết không được để trống.',
            'Content.required'  => 'Nội dung bài viết không được để trống.',
            'ImageUrl.required' => 'Vui lòng chọn ảnh đại diện.',
            'ImageUrl.image'    => 'File tải lên phải là ảnh.',
            'ImageUrl.max'      => 'Ảnh không được vượt quá 2MB.',
            'Status.required'   => 'Trạng thái không được để trống.',
            'Status.in'         => 'Trạng thái không hợp lệ (Draft/Published/Hidden).',
            'Slug.unique'       => 'Slug đã tồn tại, vui lòng chọn slug khác.',
        ];
    }
}