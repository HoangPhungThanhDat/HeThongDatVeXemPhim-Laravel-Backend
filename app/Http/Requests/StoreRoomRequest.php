<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // ✅ Kiểm tra xem đây là request Thêm mới (POST) hay Cập nhật (PUT/PATCH)
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch');

        return [
            'CinemaId' => 'required|integer|exists:cinemas,CinemaId',
            
            // Nếu là Update, cho phép bỏ qua trường Name (sometimes)
            'Name'     => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            
            // Tương tự cho RoomType
            'RoomType' => $isUpdate ? 'sometimes|required|string|in:2D,3D,4DX,IMAX' : 'required|string|in:2D,3D,4DX,IMAX',
            
            // Status bắt buộc và phải nằm trong danh sách cho phép
            'Status'   => 'required|string|in:Active,Inactive,Maintenance',
            
            // SeatCount không cần validate vì có thể tự động tính
            'SeatCount' => 'nullable|integer|min:0',
        ];
    }
}