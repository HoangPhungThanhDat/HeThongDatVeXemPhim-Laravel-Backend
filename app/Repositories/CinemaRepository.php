<?php

namespace App\Repositories;

use App\Models\Cinema;
use App\Models\Showtime;
use App\Models\Schedule;  // ✅ [THÊM MỚI] import Schedule
use Carbon\Carbon;         // ✅ [THÊM MỚI] import Carbon

class CinemaRepository
{
    public function getAll()
    {
        return Cinema::all();
    }

    public function getById($id)
    {
        return Cinema::findOrFail($id);
    }

    public function create(array $data)
    {
        return Cinema::create($data);
    }

    public function update($id, array $data)
    {
        $cinema = Cinema::findOrFail($id);
        $cinema->update($data);
        return $cinema;
    }

    public function delete($id)
    {
        $cinema = Cinema::findOrFail($id);
        return $cinema->delete();
    }

    // ===============================
    // API LỊCH CHIẾU THEO RẠP + NGÀY
    // ===============================

    public function getShowtimesByCinema($cinemaId, $date)
    {
        // ✅ [THÊM MỚI] Tự sinh Showtime từ lịch định kỳ trước khi query
        $this->generateShowtimesFromSchedules($cinemaId, $date);

        return Showtime::with([
            'movie',
            'movie.directors',
            'movie.actors',
            'room'
        ])
        ->whereHas('room', function ($q) use ($cinemaId) {
            $q->where('CinemaId', $cinemaId);
        })
        ->whereDate('StartTime', $date)
        ->orderBy('StartTime')
        ->get();
    }

    // ===============================
    // ✅ [THÊM MỚI] Sinh Showtime từ Schedule định kỳ
    // Gọi mỗi khi user chọn ngày xem lịch chiếu
    // ===============================

    private function generateShowtimesFromSchedules($cinemaId, $date)
    {
        $carbon    = Carbon::parse($date);
        $dayOfWeek = $carbon->dayOfWeek; // Carbon: 0=Sun, 1=Mon, ..., 6=Sat

        // Map Carbon dayOfWeek → giá trị DaysOfWeek bạn lưu trong DB
        // DB lưu: 2=Thứ2, 3=Thứ3, 4=Thứ4, 5=Thứ5, 6=Thứ6, 7=Thứ7, 8=CN
                $dayMap = [
            0 => 'Sun',
            1 => 'Mon',
            2 => 'Tue',
            3 => 'Wed',
            4 => 'Thu',
            5 => 'Fri',
            6 => 'Sat',
        ];
        $dayValue = $dayMap[$dayOfWeek];

        // Lấy tất cả Schedule định kỳ thuộc rạp này, còn hiệu lực, đúng thứ
        $schedules = Schedule::whereHas('room', function ($q) use ($cinemaId) {
                $q->where('CinemaId', $cinemaId);
            })
            ->where('Status', 'Active')
            ->where('StartDate', '<=', $date)
            ->where(function ($q) use ($date) {
                // EndDate null = chiếu vô thời hạn
                $q->whereNull('EndDate')->orWhere('EndDate', '>=', $date);
            })
            ->get()
            ->filter(function ($schedule) use ($dayValue) {
            $days = array_map('trim', explode(',', $schedule->DaysOfWeek));
            return in_array($dayValue, $days);
            });

        foreach ($schedules as $schedule) {
            // Ghép ngày được chọn + giờ từ schedule
            $startTime = Carbon::parse(
                $date . ' ' . $schedule->StartTime->format('H:i:s')
            );
            $endTime = Carbon::parse(
                $date . ' ' . $schedule->EndTime->format('H:i:s')
            );

            // Chỉ tạo nếu chưa tồn tại — tránh duplicate
            $exists = Showtime::where('RoomId', $schedule->RoomId)
                ->where('MovieId', $schedule->MovieId)
                ->where('StartTime', $startTime)
                ->exists();

            if (!$exists) {
                Showtime::create([
                    'MovieId'   => $schedule->MovieId,
                    'RoomId'    => $schedule->RoomId,
                    'StartTime' => $startTime,
                    'EndTime'   => $endTime,
                    'Price'     => $schedule->Price,
                    'Status' => 'Scheduled',
                    'CreatedAt' => now(),
                    'UpdatedAt' => now(),
                    'CreatedBy' => $schedule->CreatedBy,
                    'UpdatedBy' => $schedule->UpdatedBy,
                ]);
            }
        }
    }

    // ===============================
    // API LỊCH CHIẾU THEO PHIM
    // ===============================

    public function getShowtimesByMovie($movieId)
    {
        $movie = \App\Models\Movie::where('MovieId', $movieId)
            ->orWhere('Slug', $movieId)
            ->first();

        if (!$movie) return collect();

        return Showtime::with([
            'room.cinema',
            'movie.directors',
            'movie.actors'
        ])
        ->where('MovieId', $movie->MovieId)
        ->orderBy('StartTime')
        ->get()
        ->filter(fn($s) => $s->room && $s->room->cinema);
    }
}