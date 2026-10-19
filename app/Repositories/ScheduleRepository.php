<?php

namespace App\Repositories;

use App\Models\Schedule;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\Showtimeseat;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ScheduleRepository
{
    const CACHE_TIME = 300; // 5 phút

    /**
     * Lấy tất cả Schedule - ✅ ĐÃ CACHE
     */
    public function getAll()
    {
        return Cache::remember('schedules_all', self::CACHE_TIME, function () {
            return Schedule::with(['movie', 'room.cinema'])->orderBy('CreatedAt', 'desc')->get();
        });
    }

    /**
     * Tìm Schedule theo ID - ✅ ĐÃ CACHE
     */
    public function getById($id)
    {
        return Cache::remember("schedule_detail_{$id}", self::CACHE_TIME, function () use ($id) {
            return Schedule::with(['movie', 'room.cinema', 'showtimes'])->findOrFail($id);
        });
    }

    /**
     * Tạo mới - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $schedule = Schedule::create($data);
        $this->clearCache();
        return $schedule;
    }

    /**
     * Cập nhật - ✅ XÓA CACHE
     */
    public function update($id, array $data)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->update($data);
        $this->clearCache($id);
        return $schedule;
    }

    /**
     * Xóa - ✅ XÓA CACHE
     */
    public function delete($id)
    {
        $schedule = Schedule::findOrFail($id);
        $result = $schedule->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Bulk Insert Showtimes (Chuyển từ Service xuống)
     * Trả về danh sách Showtime vừa tạo (có ID)
     */
    public function insertShowtimes(array $data)
    {
        // Insert dữ liệu
        Showtime::insert($data);

        // Lấy lại danh sách vừa tạo để lấy ID
        // (Cách này nhanh và an toàn trong transaction)
        return Showtime::orderBy('ShowtimeId', 'desc')
            ->take(count($data))
            ->get();
    }

    /**
     * ✅ Lấy danh sách ID ghế đang hoạt động của phòng
     */
    public function getActiveSeatIdsByRoom($roomId)
    {
        return Seat::where('RoomId', $roomId)
            ->where('Status', '!=', 'Inactive')
            ->pluck('SeatId')
            ->toArray();
    }

    /**
     * ✅ Bulk Insert ShowtimeSeats (Chuyển từ Service xuống)
     */
    public function insertShowtimeSeats(array $data)
    {
        // Chia nhỏ mảng để tránh giới hạn SQL (VD: 1000 dòng/lần)
        $chunks = array_chunk($data, 500);
        foreach ($chunks as $chunk) {
            Showtimeseat::insert($chunk);
        }
        return true;
    }

    /**
     * ✅ Xóa Cache liên quan
     */
    public function clearCache($id = null)
    {
        Cache::forget('schedules_all');
        if ($id) {
            Cache::forget("schedule_detail_{$id}");
        }
    }
}