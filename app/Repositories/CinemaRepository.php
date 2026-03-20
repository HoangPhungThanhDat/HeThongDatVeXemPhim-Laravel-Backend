<?php

// namespace App\Repositories;

// use App\Models\Cinema;

// class CinemaRepository
// {
//     /**
//      * Lấy tất cả Cinema
//      */
//     public function getAll()
//     {
//         return Cinema::all();
//     }

//     /**
//      * Tìm Cinema theo ID
//      */
//     public function getById($id)
//     {
//         return Cinema::findOrFail($id);
//     }

//     /**
//      * Tạo mới Cinema
//      */
//     public function create(array $data)
//     {
//         return Cinema::create($data);
//     }

//     /**
//      * Cập nhật Cinema
//      */
//     public function update($id, array $data)
//     {
//         $cinema = Cinema::findOrFail($id);
//         $cinema->update($data);
//         return $cinema;
//     }

//     /**
//      * Xóa cinema
//      */
//     public function delete($id)
//     {
//         $cinema = Cinema::findOrFail($id);
//         return $cinema->delete();
//     }
// }






























namespace App\Repositories;

use App\Models\Cinema;
//lich-chieu-phim-ngay-15-3-2026
use App\Models\Showtime;

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
    // API LỊCH CHIẾU THEO RẠP
    // ===============================

    // API LỊCH CHIẾU THEO RẠP + Đạo diễn + Diễn viên
// CinemaRepository.php
public function getShowtimesByCinema($cinemaId, $date)
    {
        return Showtime::with([
            'movie',
            'movie.directors', // Role=Director & Status=Active (filter trong Movie model)
            'movie.actors',    // Role=Actor & Status=Active
            'room'
        ])
        ->whereHas('room', function ($q) use ($cinemaId) {
            $q->where('CinemaId', $cinemaId);
        })
        ->whereDate('StartTime', $date)
        ->orderBy('StartTime')
        ->get();
    }
}

