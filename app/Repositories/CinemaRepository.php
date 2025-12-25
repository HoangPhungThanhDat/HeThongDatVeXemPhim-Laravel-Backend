<?php

namespace App\Repositories;

use App\Models\Cinema;

class CinemaRepository
{
    /**
     * Lấy tất cả Cinema
     */
    public function getAll()
    {
        return Cinema::all();
    }

    /**
     * Tìm Cinema theo ID
     */
    public function getById($id)
    {
        return Cinema::findOrFail($id);
    }

    /**
     * Tạo mới Cinema
     */
    public function create(array $data)
    {
        return Cinema::create($data);
    }

    /**
     * Cập nhật Cinema
     */
    public function update($id, array $data)
    {
        $cinema = Cinema::findOrFail($id);
        $cinema->update($data);
        return $cinema;
    }

    /**
     * Xóa cinema
     */
    public function delete($id)
    {
        $cinema = Cinema::findOrFail($id);
        return $cinema->delete();
    }
}
