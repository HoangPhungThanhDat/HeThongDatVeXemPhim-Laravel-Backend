<?php

namespace App\Repositories;

use App\Models\Seat;
use App\Models\Room;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SeatRepository
{
    const CACHE_TIME = 1800; // 30 phút (Ghế ít thay đổi)

    /**
     * Lấy tất cả Seat - ✅ ĐÃ CACHE
     */
    public function getAll()
    {
        return Cache::remember('seats_all', self::CACHE_TIME, function () {
            return Seat::orderBy('RoomId')->orderBy('Row')->orderBy('Number')->get();
        });
    }

    /**
     * Tìm Seat theo ID - ✅ ĐÃ CACHE
     */
    public function getById($id)
    {
        return Cache::remember("seat_detail_{$id}", self::CACHE_TIME, function () use ($id) {
            return Seat::findOrFail($id);
        });
    }

    /**
     * Tạo mới Seat - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $seat = Seat::create($data);
        $this->clearCache();
        return $seat;
    }

    /**
     * Tạo nhiều Seat cùng lúc - ✅ XÓA CACHE
     */
    public function bulkCreate(array $seats)
    {
        return DB::transaction(function () use ($seats) {
            $createdSeats = [];
            
            foreach ($seats as $seatData) {
                unset($seatData['SeatId']);
                $createdSeats[] = Seat::create($seatData);
            }
            
            $this->clearCache();
            return $createdSeats;
        });
    }

    /**
     * Cập nhật Seat - ✅ XÓA CACHE
     */
    public function update($id, array $data)
    {
        $seat = Seat::findOrFail($id);
        $seat->update($data);
        $this->clearCache($id);
        return $seat;
    }

    /**
     * Xóa Seat - ✅ XÓA CACHE
     */
    public function delete($id)
    {
        $seat = Seat::findOrFail($id);
        $result = $seat->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Xóa toàn bộ ghế của 1 phòng
     */
    public function deleteByRoomId($roomId)
    {
        $result = Seat::where('RoomId', $roomId)->delete();
        $this->clearCache();
        return $result;
    }

    /**
     * ✅ Cập nhật số ghế của phòng (Chuyển từ Service xuống)
     */
    public function updateRoomSeatCount($roomId)
    {
        $room = Room::find($roomId);
        if ($room) {
            $seatCount = Seat::where('RoomId', $roomId)->count();
            $room->SeatCount = $seatCount;
            $room->save();
            
            // Xóa cache của Room vì SeatCount thay đổi
            Cache::forget('rooms_all');
            Cache::forget("room_detail_{$roomId}");
            Cache::forget("room_detail_rel_{$roomId}");
        }
    }

    /**
     * ✅ Xóa Cache liên quan
     */
    public function clearCache($id = null)
    {
        Cache::forget('seats_all');
        if ($id) {
            Cache::forget("seat_detail_{$id}");
        }
    }
}