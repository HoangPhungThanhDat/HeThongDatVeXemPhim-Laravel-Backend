<?php

namespace App\Repositories;

use App\Models\Seat;
use Illuminate\Support\Facades\DB;
class SeatRepository
{
    /**
     * Lấy tất cả Seat
     */
    public function getAll()
    {
        return Seat::all();
    }

    /**
     * Tìm Seat theo ID
     */
    public function getById($id)
    {
        return Seat::findOrFail($id);
    }

    /**
     * Tạo mới Seat
     */
    public function create(array $data)
    {
        return Seat::create($data);
    }

    /**
     * Tạo nhiều Seat cùng lúc
     */
    public function bulkCreate(array $seats)
    {
        return DB::transaction(function () use ($seats) {
            $createdSeats = [];
            
            foreach ($seats as $seatData) {
                // KHÔNG tạo SeatId vì nó là auto increment
                // Xóa SeatId nếu có trong data
                unset($seatData['SeatId']);
                
                $createdSeats[] = Seat::create($seatData);
            }
            
            return $createdSeats;
        });
    }

    /**
     * Cập nhật Seat
     */
    public function update($id, array $data)
    {
        $seat = Seat::findOrFail($id);
        $seat->update($data);
        return $seat;
    }

    /**
     * Xóa Seat
     */
    public function delete($id)
    {
        $seat = Seat::findOrFail($id);
        return $seat->delete();
    }
}
