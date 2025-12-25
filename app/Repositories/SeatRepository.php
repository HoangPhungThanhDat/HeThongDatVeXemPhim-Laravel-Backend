<?php

namespace App\Repositories;

use App\Models\Seat;

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
