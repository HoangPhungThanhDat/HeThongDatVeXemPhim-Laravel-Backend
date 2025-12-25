<?php

namespace App\Repositories;

use App\Models\Loginhistory;

class LoginhistoryRepository

{
    /**
     * Lấy tất cả Loginhistory
     */
    public function getAll()
    {
        return Loginhistory::all();
    }

    /**
     * Tìm Loginhistory theo ID
     */
    public function getById($id)
    {
        return Loginhistory::findOrFail($id);
    }

    /**
     * Tạo mới Loginhistory
     */
    public function create(array $data)
    {
        return Loginhistory::create($data);
    }

    /**
     * Cập nhật Loginhistory
     */
    public function update($id, array $data)
    {
        $loginhistory = Loginhistory::findOrFail($id);
        $loginhistory->update($data);
        return $loginhistory;
    }

    /**
     * Xóa loginhistory
     */
    public function delete($id)
    {
        $loginhistory = Loginhistory::findOrFail($id);
        return $loginhistory->delete();
    }
}
