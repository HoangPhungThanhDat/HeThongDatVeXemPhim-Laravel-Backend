<?php

namespace App\Repositories;

use App\Models\Showtimeseat;

class ShowtimeseatRepository
{
    /**
     * Lấy tất cả Showtimeseat
     */
    public function getAll()
    {
        return Showtimeseat::all();
    }

    /**
     * Tìm Showtimeseat theo ID
     */
    public function getById($id)
    {
        return Showtimeseat::findOrFail($id);
    }

    /**
     * Tạo mới Showtimeseat
     */
    public function create(array $data)
    {
        return Showtimeseat::create($data);
    }

    /**
     * Cập nhật Showtimeseat
     */
    public function update($id, array $data)
    {
        $showtimeseat = Showtimeseat::findOrFail($id);
        $showtimeseat->update($data);
        return $showtimeseat;
    }

    /**
     * Xóa Showtimeseat
     */
    public function delete($id)
    {
        $showtimeseat = Showtimeseat::findOrFail($id);
        return $showtimeseat->delete();
    }
}
