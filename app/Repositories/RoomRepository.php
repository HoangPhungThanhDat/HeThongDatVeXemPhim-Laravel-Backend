<?php

namespace App\Repositories;

use App\Models\Room;

class RoomRepository
{
    /**
     * Lấy tất cả Room
     */
    public function getAll()
    {
        return Room::all();
    }

    /**
     * Tìm Room theo ID
     */
    public function getById($id)
    {
        return Room::findOrFail($id);
    }

    /**
     * Tạo mới Room
     */
    public function create(array $data)
    {
        return Room::create($data);
    }

    /**
     * Cập nhật Room
     */
    public function update($id, array $data)
    {
        $room = Room::findOrFail($id);
        $room->update($data);
        return $room;
    }

    /**
     * Xóa Room
     */
    public function delete($id)
    {
        $room = Room::findOrFail($id);
        return $room->delete();
    }
}
