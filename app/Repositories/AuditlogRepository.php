<?php

namespace App\Repositories;

use App\Models\Auditlog;

class AuditlogRepository
{
    /**
     * Lấy tất cả Auditlog
     */
    public function getAll()
    {
        return Auditlog::all();
    }

    /**
     * Tìm Auditlog theo ID
     */
    public function getById($id)
    {
        return Auditlog::findOrFail($id);
    }

    /**
     * Tạo mới Auditlog
     */
    public function create(array $data)
    {
        return Auditlog::create($data);
    }

    /**
     * Cập nhật Auditlog
     */
    public function update($id, array $data)
    {
        $auditlog = Auditlog::findOrFail($id);
        $auditlog->update($data);
        return $auditlog;
    }

    /**
     * Xóa auditlog
     */
    public function delete($id)
    {
        $auditlog = Auditlog::findOrFail($id);
        return $auditlog->delete();
    }
}
