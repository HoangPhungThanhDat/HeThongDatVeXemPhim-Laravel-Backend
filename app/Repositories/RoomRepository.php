<?php

namespace App\Repositories;

use App\Models\Room;
use App\Models\Seat;
use Illuminate\Support\Facades\Cache;

class RoomRepository
{
    const CACHE_TIME = 1800; // Cache 30 phút (Phòng chiếu ít thay đổi)

    /**
     * Lấy tất cả Room - ✅ ĐÃ CACHE
     */
    public function getAll()
    {
        return Cache::remember('rooms_all', self::CACHE_TIME, function () {
            return Room::with('cinema')->orderBy('Name', 'asc')->get();
        });
    }

    /**
     * Tìm Room theo ID - ✅ ĐÃ CACHE
     */
    public function getById($id)
    {
        return Cache::remember("room_detail_{$id}", self::CACHE_TIME, function () use ($id) {
            return Room::with('cinema')->findOrFail($id);
        });
    }

    /**
     * Tạo mới Room - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $room = Room::create($data);
        $this->clearCache();
        return $room;
    }

    /**
     * Cập nhật Room - ✅ XÓA CACHE
     */
    public function update($id, array $data)
    {
        $room = Room::findOrFail($id);
        $room->update($data);
        $this->clearCache($id);
        return $room;
    }

    /**
     * Xóa Room - ✅ XÓA CACHE
     */
    public function delete($id)
    {
        $room = Room::findOrFail($id);
        $result = $room->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Xóa tất cả ghế của phòng (Đã chuyển từ Service xuống)
     */
    public function deleteSeatsByRoomId($roomId)
    {
        return Seat::where('RoomId', $roomId)->delete();
    }

    /**
     * ✅ Xóa Cache liên quan
     */
    public function clearCache($id = null)
    {
        Cache::forget('rooms_all');
        if ($id) {
            Cache::forget("room_detail_{$id}");
        }
    }
}