<?php
namespace App\Repositories;

use App\Models\Showtimeseat;
use App\Models\Seat;
use Illuminate\Support\Str;

class ShowtimeseatRepository
{
    public function getAll()
    {
        return Showtimeseat::all();
    }

    public function getById($id)
    {
        return Showtimeseat::findOrFail($id);
    }

    public function create(array $data)
    {
        return Showtimeseat::create($data);
    }

    public function update($id, array $data)
    {
        $showtimeseat = Showtimeseat::findOrFail($id);
        $showtimeseat->update($data);
        return $showtimeseat;
    }

    public function delete($id)
    {
        $showtimeseat = Showtimeseat::findOrFail($id);
        return $showtimeseat->delete();
    }

    // ✅ THÊM MỚI: Lấy ghế theo ShowtimeId — nhóm theo hàng
    public function getSeatsByShowtimeId($showtimeId)
    {
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
            ->sortKeys(); // Sắp xếp A, B, C...
    }

    // ✅ THÊM MỚI: Auto-generate ghế khi tạo showtime mới
    public function generateSeatsForShowtime($showtimeId, $roomId, $createdBy = null)
    {
        $seats = Seat::where('RoomId', $roomId)
            ->where('Status', 'Available')
            ->get();

        if ($seats->isEmpty()) return;

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
    }
}