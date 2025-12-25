<?php

namespace App\Repositories;

use App\Models\Notification;

class NotificationRepository
{
    /**
     * Lấy tất cả Notification
     */
    public function getAll()
    {
        return Notification::all();
    }

    /**
     * Tìm Notification theo ID
     */
    public function getById($id)
    {
        return Notification::findOrFail($id);
    }

    /**
     * Tạo mới Notification
     */
    public function create(array $data)
    {
        return Notification::create($data);
    }

    /**
     * Cập nhật Notification
     */
    public function update($id, array $data)
    {
        $notification = Notification::findOrFail($id);
        $notification->update($data);
        return $notification;
    }

    /**
     * Xóa Notification
     */
    public function delete($id)
    {
        $notification = Notification::findOrFail($id);
        return $notification->delete();
    }
}
