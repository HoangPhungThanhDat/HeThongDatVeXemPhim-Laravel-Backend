<?php

namespace App\Repositories;

use App\Models\Schedule;

class ScheduleRepository
{
    /**
     * Lấy tất cả Schedule
     */
    public function getAll()
    {
        return Schedule::all();
    }

    /**
     * Tìm Schedule theo ID
     */
    public function getById($id)
    {
        return Schedule::findOrFail($id);
    }

    /**
     * Tạo mới Schedule
     */
    public function create(array $data)
    {
        return Schedule::create($data);
    }

    /**
     * Cập nhật Schedule
     */
    public function update($id, array $data)
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->update($data);
        return $schedule;
    }

    /**
     * Xóa schedule
     */
    public function delete($id)
    {
        $schedule = Schedule::findOrFail($id);
        return $schedule->delete();
    }
}
