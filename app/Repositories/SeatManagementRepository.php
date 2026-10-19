<?php

namespace App\Repositories;

use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SeatManagementRepository
{
    const CACHE_TIME = 300; // 5 phút

    public function getCinemas()
    {
        return Cache::remember('sm_cinemas', self::CACHE_TIME, function () {
            return Cinema::where('Status', 'Active')
                ->select('CinemaId as id', 'Name as name', 'City as city')
                ->get();
        });
    }

    public function getRooms($cinemaId = null)
    {
        $key = "sm_rooms_" . ($cinemaId ?? 'all');
        return Cache::remember($key, self::CACHE_TIME, function () use ($cinemaId) {
            $query = Room::where('Status', 'Active');
            if ($cinemaId) $query->where('CinemaId', $cinemaId);
            return $query->select('RoomId as id', 'Name as name', 'CinemaId', 'RoomType', 'SeatCount')->get();
        });
    }

    public function getShowtimes($cinemaId = null, $roomId = null)
    {
        $key = "sm_showtimes_" . ($cinemaId ?? 'all') . "_" . ($roomId ?? 'all');
        return Cache::remember($key, self::CACHE_TIME, function () use ($cinemaId, $roomId) {
            $query = Showtime::with(['movie', 'room'])->where('Status', 'Scheduled');
            if ($roomId) $query->where('RoomId', $roomId);
            if ($cinemaId) {
                $query->whereHas('room', fn($q) => $q->where('CinemaId', $cinemaId));
            }
            return $query->get()->map(function ($st) {
                return [
                    'id' => $st->ShowtimeId, 'room_id' => $st->RoomId,
                    'movie_title' => $st->movie->Title ?? 'Unknown',
                    'room_name' => $st->room->Name ?? 'Unknown',
                    'start_time' => $st->StartTime, 'end_time' => $st->EndTime,
                    'price' => $st->Price, 'status' => $st->Status,
                ];
            });
        });
    }

    public function getShowtimeById($showtimeId)
    {
        return Showtime::with('room')->find($showtimeId); // Không cache vì cần dữ liệu chính xác
    }

    public function getShowtimeSeats($showtimeId)
    {
        return ShowtimeSeat::with(['seat'])->where('ShowtimeId', $showtimeId)->get();
    }

    public function getPaginatedShowtimeSeats($showtimeId, $perPage, $page)
    {
        return ShowtimeSeat::with(['seat'])
            ->where('ShowtimeId', $showtimeId)
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function countShowtimeSeats($showtimeId)
    {
        return ShowtimeSeat::where('ShowtimeId', $showtimeId)->count();
    }

    public function getTicketsBySeats($showtimeId, array $seatIds)
    {
        return Ticket::with('user')
            ->where('ShowtimeId', $showtimeId)
            ->whereIn('SeatId', $seatIds)
            ->whereIn('Status', ['Paid', 'Pending'])
            ->get()
            ->keyBy('SeatId');
    }

    public function generateSeatsForRoom($roomId, $showtimeId)
    {
        $roomSeats = Seat::where('RoomId', $roomId)->get();
        if ($roomSeats->isEmpty()) return false;

        $insertData = $roomSeats->map(function ($roomSeat) use ($showtimeId) {
            return [
                'ShowtimeId' => $showtimeId,
                'SeatId' => $roomSeat->SeatId,
                'Status' => 'Available',
                'CreatedAt' => now(),
                'UpdatedAt' => now(),
            ];
        })->toArray();

        ShowtimeSeat::insert($insertData);
        return true;
    }

    public function getSeatStats($showtimeId)
    {
        $allSeats = ShowtimeSeat::where('ShowtimeId', $showtimeId)->get();
        $stats = ['trong' => 0, 'dat' => 0, 'giu' => 0, 'loi' => 0, 'baoTri' => 0];
        
        $map = ['Available' => 'trong', 'Reserved' => 'dat', 'Held' => 'giu', 'Broken' => 'loi', 'Inactive' => 'baoTri'];
        
        foreach ($allSeats as $ss) {
            $status = $map[$ss->Status] ?? 'trong';
            if (isset($stats[$status])) $stats[$status]++;
        }
        return $stats;
    }

    public function generateSeats($showtimeId)
    {
        DB::beginTransaction();
        try {
            $showtime = Showtime::with('room')->find($showtimeId);
            $room = $showtime->room;
            $roomSeats = Seat::where('RoomId', $room->RoomId)->get();

            if ($roomSeats->isEmpty()) {
                $seatCount = $room->SeatCount ?? 50;
                $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                $seatsPerRow = ceil($seatCount / count($rows));
                $createdSeats = [];
                for ($i = 0; $i < count($rows) && count($createdSeats) < $seatCount; $i++) {
                    $row = $rows[$i];
                    for ($j = 1; $j <= $seatsPerRow && count($createdSeats) < $seatCount; $j++) {
                        $seat = Seat::create([
                            'RoomId' => $room->RoomId, 'Row' => $row, 'Number' => $j,
                            'SeatType' => $j % 5 == 0 ? 'VIP' : 'Normal', 'Status' => 'Available',
                            'CreatedAt' => now(), 'UpdatedAt' => now(),
                        ]);
                        $createdSeats[] = $seat;
                    }
                }
                $roomSeats = collect($createdSeats);
                $room->SeatCount = count($createdSeats);
                $room->save();
            }

            $deleted = ShowtimeSeat::where('ShowtimeId', $showtimeId)->delete();

            $insertData = $roomSeats->map(function ($seat) use ($showtimeId) {
                return [
                    'ShowtimeId' => $showtimeId, 'SeatId' => $seat->SeatId,
                    'Status' => 'Available', 'CreatedAt' => now(), 'UpdatedAt' => now(),
                ];
            })->toArray();

            if (!empty($insertData)) ShowtimeSeat::insert($insertData);

            DB::commit();

            // Xóa cache ghế
            $this->clearCache($showtimeId);

            return [
                'total_seats' => count($insertData),
                'deleted_old_seats' => $deleted,
                'room_seats' => $roomSeats->count(),
                'room_name' => $room->Name,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateSeat($seatId, $status)
    {
        $showtimeSeat = ShowtimeSeat::find($seatId);
        if (!$showtimeSeat) return false;

        $oldStatus = $showtimeSeat->Status;
        $showtimeSeat->Status = $status;
        $showtimeSeat->UpdatedAt = now();
        $showtimeSeat->UpdatedBy = auth()->user()->UserId ?? null;
        $showtimeSeat->save();

        $this->clearCache($showtimeSeat->ShowtimeId);

        return [
            'id' => $showtimeSeat->ShowtimeSeatId, 'old_status' => $oldStatus,
            'new_status' => $status,
            'status' => $this->mapStatusToLocal($showtimeSeat->Status),
            'status_raw' => $showtimeSeat->Status,
        ];
    }

    public function resetSeat($seatId)
    {
        $showtimeSeat = ShowtimeSeat::find($seatId);
        if (!$showtimeSeat) return false;

        $oldStatus = $showtimeSeat->Status;
        $showtimeSeat->Status = 'Available';
        $showtimeSeat->UpdatedAt = now();
        $showtimeSeat->UpdatedBy = auth()->user()->UserId ?? null;
        $showtimeSeat->save();

        $this->clearCache($showtimeSeat->ShowtimeId);

        return [
            'id' => $showtimeSeat->ShowtimeSeatId, 'old_status' => $oldStatus,
            'new_status' => 'Available', 'status' => 'trong',
        ];
    }

    public function deleteSeat($seatId)
    {
        $showtimeSeat = ShowtimeSeat::find($seatId);
        if (!$showtimeSeat) return false;

        $showtimeId = $showtimeSeat->ShowtimeId;
        $showtimeSeat->delete();

        $this->clearCache($showtimeId);

        return true;
    }

    public function deleteAllSeatsByShowtime($showtimeId)
    {
        $deleted = ShowtimeSeat::where('ShowtimeId', $showtimeId)->delete();
        $this->clearCache($showtimeId);
        return $deleted;
    }

    private function mapStatusToLocal($status)
    {
        $map = [
            'Available' => 'trong', 'Reserved' => 'dat', 'Held' => 'giu',
            'Broken' => 'loi', 'Inactive' => 'baoTri',
        ];
        return $map[$status] ?? 'trong';
    }

    private function clearCache($showtimeId = null)
    {
        Cache::forget('sm_cinemas');
        // Xóa cache rooms, showtimes nếu cần (dùng tag hoặc pattern)
    }
}