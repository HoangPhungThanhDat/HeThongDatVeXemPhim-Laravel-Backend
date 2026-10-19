<?php

namespace App\Repositories;

use App\Models\Showtimeseat;
use App\Models\Showtime;
use App\Models\Seat;
use Illuminate\Support\Facades\Cache;

class ShowtimeseatRepository
{
    // Cache ngắn vì trạng thái ghế thay đổi liên tục
    const CACHE_TIME = 60; // 1 phút

    /**
     * Lấy tất cả - ✅ ĐÃ CACHE
     */
    public function getAll()
    {
        return Cache::remember('showtimeseats_all', self::CACHE_TIME, function () {
            return Showtimeseat::with(['seat', 'showtime.movie'])->get();
        });
    }

    /**
     * ✅ Lấy dữ liệu phân trang (Chuyển câu query từ Controller xuống)
     */
    public function getPaged($page, $limit, $showtimeId = '', $status = '')
    {
        $cacheKey = "showtimeseats_paged_p{$page}_l{$limit}_" . md5($showtimeId . $status);

        return Cache::remember($cacheKey, self::CACHE_TIME, function () use ($page, $limit, $showtimeId, $status) {
            $query = Showtimeseat::with(['seat', 'showtime.movie', 'showtime.room']);

            if ($showtimeId) {
                $query->where('ShowtimeId', $showtimeId);
            }

            if ($status) {
                $query->where('Status', $status);
            }

            $total      = $query->count();
            $totalPages = (int) ceil($total / $limit);
            $items      = $query->orderBy('ShowtimeSeatId', 'asc')
                                ->skip(($page - 1) * $limit)
                                ->take($limit)
                                ->get();

            return [
                'data'       => $items,
                'total'      => $total,
                'page'       => $page,
                'limit'      => $limit,
                'totalPages' => $totalPages,
            ];
        });
    }

    /**
     * Tìm theo ID - ✅ ĐÃ CACHE
     */
    public function getById($id)
    {
        return Cache::remember("showtimeseat_detail_{$id}", self::CACHE_TIME, function () use ($id) {
            return Showtimeseat::findOrFail($id);
        });
    }

    /**
     * Tạo mới - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $showtimeseat = Showtimeseat::create($data);
        $this->clearCache($showtimeseat->ShowtimeId);
        return $showtimeseat;
    }

    /**
     * Cập nhật - ✅ XÓA CACHE
     */
    public function update($id, array $data)
    {
        $showtimeseat = Showtimeseat::findOrFail($id);
        $showtimeseat->update($data);
        $this->clearCache($showtimeseat->ShowtimeId, $id);
        return $showtimeseat;
    }

    /**
     * Xóa - ✅ XÓA CACHE
     */
    public function delete($id)
    {
        $showtimeseat = Showtimeseat::findOrFail($id);
        $showtimeId = $showtimeseat->ShowtimeId;
        $result = $showtimeseat->delete();
        $this->clearCache($showtimeId, $id);
        return $result;
    }

    /**
     * Lấy ghế theo ShowtimeId — nhóm theo hàng
     */
    public function getSeatsByShowtimeId($showtimeId)
    {
        return Cache::remember("showtime_{$showtimeId}_seats_map", self::CACHE_TIME, function () use ($showtimeId) {
            return Showtimeseat::with('seat')
                ->where('ShowtimeId', $showtimeId)
                ->whereHas('seat', function ($q) {
                    $q->where('Status', '!=', 'Broken');
                })
                ->get()
                ->map(function ($ss) {
                    return [
                        'ShowtimeSeatId' => $ss->ShowtimeSeatId,
                        'SeatId'         => $ss->SeatId,
                        'Row'            => $ss->seat->Row,
                        'Number'         => $ss->seat->Number,
                        'SeatType'       => $ss->seat->SeatType,
                        'Status'         => $ss->Status,
                    ];
                })
                ->groupBy('Row')
                ->map(fn($group) => $group->sortBy('Number')->values())
                ->sortKeys();
        });
    }

    /**
     * ✅ Lấy thông tin Showtime (để sinh ghế)
     */
    public function getShowtime($showtimeId)
    {
        return Showtime::find($showtimeId);
    }

    /**
     * Auto-generate ghế khi tạo showtime mới
     */
    public function generateSeatsForShowtime($showtimeId, $roomId, $createdBy = null)
    {
        $seats = Seat::where('RoomId', $roomId)
            ->where('Status', 'Available')
            ->get();

        if ($seats->isEmpty()) return false;

        $now  = now();
        $rows = $seats->map(fn($seat) => [
            'ShowtimeId' => $showtimeId,
            'SeatId'     => $seat->SeatId,
            'Status'     => 'Available',
            'CreatedAt'  => $now,
            'UpdatedAt'  => $now,
            'CreatedBy'  => $createdBy,
            'UpdatedBy'  => $createdBy,
        ])->toArray();

        Showtimeseat::insert($rows);

        // Xóa cache của showtime này
        $this->clearCache($showtimeId);

        return true;
    }

    /**
     * ✅ Xóa Cache liên quan
     */
    public function clearCache($showtimeId = null, $id = null)
    {
        Cache::forget('showtimeseats_all');
        
        // Xóa cache phân trang
        for ($page = 1; $page <= 10; $page++) {
            for ($limit = 20; $limit <= 100; $limit += 20) {
                Cache::forget("showtimeseats_paged_p{$page}_l{$limit}_" . md5('' . ''));
                Cache::forget("showtimeseats_paged_p{$page}_l{$limit}_" . md5($showtimeId . ''));
            }
        }

        if ($id) {
            Cache::forget("showtimeseat_detail_{$id}");
        }

        if ($showtimeId) {
            Cache::forget("showtime_{$showtimeId}_seats_map");
        }
    }
}