<?php

namespace App\Repositories;

use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RoomManagementRepository
{
    const CACHE_TIME = 300; // Cache 5 phút

    public function getDashboardData()
    {
        return Cache::remember('room_management_dashboard', self::CACHE_TIME, function () {
            $cinemas = Cinema::where('Status', 'Active')
                ->orderBy('Name')
                ->get(['CinemaId', 'Name', 'Address', 'City', 'Phone', 'Email', 'ImageUrl', 'Status']);

            if ($cinemas->isEmpty()) {
                return [
                    'success' => true,
                    'message' => 'Không có rạp chiếu nào',
                    'data' => [],
                    'meta' => [
                        'total_cinemas' => 0,
                        'total_rooms' => 0,
                        'fetched_at' => now()->toDateTimeString()
                    ]
                ];
            }

            $rooms = Room::with(['cinema' => function($q) {
                $q->select('CinemaId', 'Name');
            }])
            ->select('RoomId', 'CinemaId', 'Name', 'RoomType', 'SeatCount', 'Status', 
                     'CreatedAt', 'UpdatedAt', 'CreatedBy', 'UpdatedBy')
            ->get();

            $seatStats = Seat::select(
                'RoomId',
                DB::raw('COUNT(*) as total_seats'),
                DB::raw('SUM(CASE WHEN Status = "Available" THEN 1 ELSE 0 END) as available_seats'),
                DB::raw('SUM(CASE WHEN Status IN ("Booked", "Reserved") THEN 1 ELSE 0 END) as booked_seats'),
                DB::raw('SUM(CASE WHEN Status = "Broken" THEN 1 ELSE 0 END) as broken_seats'),
                DB::raw('SUM(CASE WHEN Status = "Inactive" THEN 1 ELSE 0 END) as inactive_seats')
            )
            ->groupBy('RoomId')
            ->get()
            ->keyBy('RoomId');

            $now = now();
            $currentShowtimes = Showtime::where('StartTime', '<=', $now)
                ->where('EndTime', '>=', $now)
                ->where('Status', 'Scheduled')
                ->with(['movie' => function($q) {
                    $q->select('MovieId', 'Title');
                }])
                ->get(['ShowtimeId', 'RoomId', 'MovieId', 'StartTime', 'EndTime'])
                ->groupBy('RoomId');

            $result = [];
            foreach ($cinemas as $cinema) {
                $cinemaRooms = $rooms->where('CinemaId', $cinema->CinemaId);
                
                $roomData = [];
                foreach ($cinemaRooms as $room) {
                    $stats = $seatStats->get($room->RoomId);
                    $currentShowtime = $currentShowtimes->get($room->RoomId);
                    $movieTitle = null;
                    if ($currentShowtime && $currentShowtime->count() > 0) {
                        $movieTitle = $currentShowtime->first()->movie?->Title ?? 'Đang chiếu';
                    }

                    $roomData[] = [
                        'RoomId' => $room->RoomId,
                        'Name' => $room->Name,
                        'RoomType' => $room->RoomType,
                        'Status' => $room->Status,
                        'SeatCount' => (int)($room->SeatCount ?? 0),
                        'TotalSeats' => (int)($stats->total_seats ?? 0),
                        'AvailableSeats' => (int)($stats->available_seats ?? 0),
                        'BookedSeats' => (int)($stats->booked_seats ?? 0),
                        'BrokenSeats' => (int)($stats->broken_seats ?? 0),
                        'InactiveSeats' => (int)($stats->inactive_seats ?? 0),
                        'CurrentMovie' => $movieTitle,
                        'CreatedAt' => $room->CreatedAt,
                        'UpdatedAt' => $room->UpdatedAt,
                        'CreatedBy' => $room->CreatedBy,
                        'UpdatedBy' => $room->UpdatedBy,
                    ];
                }

                $stats = $this->calculateCinemaStats($roomData);

                $result[] = [
                    'CinemaId' => $cinema->CinemaId,
                    'Name' => $cinema->Name,
                    'Address' => $cinema->Address,
                    'City' => $cinema->City,
                    'Phone' => $cinema->Phone,
                    'Email' => $cinema->Email,
                    'ImageUrl' => $cinema->ImageUrl,
                    'Status' => $cinema->Status,
                    'Rooms' => $roomData,
                    'Stats' => $stats
                ];
            }

            return [
                'success' => true,
                'message' => 'Lấy dữ liệu thành công',
                'data' => $result,
                'meta' => [
                    'total_cinemas' => $cinemas->count(),
                    'total_rooms' => $rooms->count(),
                    'fetched_at' => now()->toDateTimeString()
                ]
            ];
        });
    }

    public function getRoomDetail($roomId)
    {
        return Cache::remember("room_management_detail_{$roomId}", self::CACHE_TIME, function () use ($roomId) {
            $room = Room::with(['cinema' => function($q) {
                $q->select('CinemaId', 'Name', 'Address');
            }])
            ->findOrFail($roomId);

            $seats = Seat::where('RoomId', $roomId)
                ->orderBy('Row')
                ->orderBy('Number')
                ->get(['SeatId', 'Row', 'Number', 'SeatType', 'Status']);

            $seatStats = [
                'total' => $seats->count(),
                'available' => $seats->where('Status', 'Available')->count(),
                'booked' => $seats->whereIn('Status', ['Booked', 'Reserved'])->count(),
                'broken' => $seats->where('Status', 'Broken')->count(),
                'inactive' => $seats->where('Status', 'Inactive')->count(),
            ];

            $now = now();
            $currentShowtime = Showtime::where('RoomId', $roomId)
                ->where('StartTime', '<=', $now)
                ->where('EndTime', '>=', $now)
                ->where('Status', 'Scheduled')
                ->with(['movie' => function($q) {
                    $q->select('MovieId', 'Title', 'PosterUrl');
                }])
                ->first();

            $seatsByRow = [];
            foreach ($seats as $seat) {
                $row = $seat->Row ?? 'A';
                if (!isset($seatsByRow[$row])) {
                    $seatsByRow[$row] = [];
                }
                $seatsByRow[$row][] = [
                    'SeatId' => $seat->SeatId,
                    'Number' => $seat->Number,
                    'SeatType' => $seat->SeatType ?? 'Normal',
                    'Status' => $seat->Status ?? 'Available',
                ];
            }
            ksort($seatsByRow);

            return [
                'success' => true,
                'message' => 'Lấy chi tiết phòng thành công',
                'data' => [
                    'RoomId' => $room->RoomId,
                    'Name' => $room->Name,
                    'RoomType' => $room->RoomType,
                    'Status' => $room->Status,
                    'SeatCount' => (int)($room->SeatCount ?? 0),
                    'Cinema' => [
                        'CinemaId' => $room->cinema->CinemaId,
                        'Name' => $room->cinema->Name,
                        'Address' => $room->cinema->Address,
                    ],
                    'SeatStats' => $seatStats,
                    'CurrentMovie' => $currentShowtime ? [
                        'MovieId' => $currentShowtime->MovieId,
                        'Title' => $currentShowtime->movie->Title ?? 'Đang chiếu',
                        'PosterUrl' => $currentShowtime->movie->PosterUrl ?? null,
                        'StartTime' => $currentShowtime->StartTime,
                        'EndTime' => $currentShowtime->EndTime,
                    ] : null,
                    'SeatsByRow' => $seatsByRow,
                    'Seats' => $seats,
                    'CreatedAt' => $room->CreatedAt,
                    'UpdatedAt' => $room->UpdatedAt,
                    'CreatedBy' => $room->CreatedBy,
                    'UpdatedBy' => $room->UpdatedBy,
                ]
            ];
        });
    }

    public function getStatistics()
    {
        return Cache::remember('room_management_statistics', self::CACHE_TIME, function () {
            $totalCinemas = Cinema::where('Status', 'Active')->count();
            $totalRooms = Room::count();
            
            $seatStats = Seat::select(
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN Status = "Available" THEN 1 ELSE 0 END) as available'),
                DB::raw('SUM(CASE WHEN Status IN ("Booked", "Reserved") THEN 1 ELSE 0 END) as booked'),
                DB::raw('SUM(CASE WHEN Status = "Broken" THEN 1 ELSE 0 END) as broken'),
                DB::raw('SUM(CASE WHEN Status = "Inactive" THEN 1 ELSE 0 END) as inactive')
            )->first();

            $roomStatus = Room::select('Status', DB::raw('COUNT(*) as count'))
                ->groupBy('Status')
                ->get()
                ->pluck('count', 'Status');

            return [
                'success' => true,
                'data' => [
                    'total_cinemas' => $totalCinemas,
                    'total_rooms' => $totalRooms,
                    'total_seats' => (int)($seatStats->total ?? 0),
                    'available_seats' => (int)($seatStats->available ?? 0),
                    'booked_seats' => (int)($seatStats->booked ?? 0),
                    'broken_seats' => (int)($seatStats->broken ?? 0),
                    'inactive_seats' => (int)($seatStats->inactive ?? 0),
                    'room_status' => [
                        'active' => (int)($roomStatus['Active'] ?? 0),
                        'inactive' => (int)($roomStatus['Inactive'] ?? 0),
                        'maintenance' => (int)($roomStatus['Maintenance'] ?? 0),
                    ]
                ]
            ];
        });
    }

    public function updateSeats($roomId, array $seats)
    {
        DB::beginTransaction();
        try {
            foreach ($seats as $seatData) {
                Seat::where('SeatId', $seatData['SeatId'])
                    ->where('RoomId', $roomId)
                    ->update([
                        'Row' => $seatData['Row'],
                        'Number' => $seatData['Number'],
                        'SeatType' => $seatData['SeatType'],
                        'Status' => $seatData['Status'],
                        'UpdatedAt' => now(),
                    ]);
            }

            DB::commit();

            // ✅ Xóa Cache vì dữ liệu ghế đã thay đổi
            $this->clearCache($roomId);

            return [
                'success' => true,
                'message' => 'Cập nhật sơ đồ ghế thành công',
                'updated_count' => count($seats)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    // ✅ THÊM HÀM CREATE ROOM (Nếu bạn chưa có ở file khác)
    public function createRoom(array $data)
    {
        $room = Room::create($data);
        $this->clearCache(); // ✅ Xóa cache dashboard
        return $room;
    }

    // ✅ THÊM HÀM UPDATE ROOM (Nếu bạn chưa có ở file khác)
    public function updateRoom($roomId, array $data)
    {
        $room = Room::findOrFail($roomId);
        $room->update($data);
        $this->clearCache($roomId); // ✅ Xóa cache dashboard và detail
        return $room;
    }

    // ✅ THÊM HÀM DELETE ROOM (Nếu bạn chưa có ở file khác)
    public function deleteRoom($roomId)
    {
        $room = Room::findOrFail($roomId);
        $result = $room->delete();
        $this->clearCache($roomId); // ✅ Xóa cache dashboard và detail
        return $result;
    }

    private function calculateCinemaStats($rooms)
    {
        $totalRooms = count($rooms);
        $activeRooms = array_filter($rooms, fn($r) => $r['Status'] === 'Active');
        $maintenanceRooms = array_filter($rooms, fn($r) => $r['Status'] === 'Maintenance');
        $inactiveRooms = array_filter($rooms, fn($r) => $r['Status'] === 'Inactive');
        
        $totalSeats = array_sum(array_column($rooms, 'TotalSeats'));
        $totalBooked = array_sum(array_column($rooms, 'BookedSeats'));
        $totalAvailable = array_sum(array_column($rooms, 'AvailableSeats'));
        $totalBroken = array_sum(array_column($rooms, 'BrokenSeats'));

        return [
            'total_rooms' => $totalRooms,
            'active_rooms' => count($activeRooms),
            'maintenance_rooms' => count($maintenanceRooms),
            'inactive_rooms' => count($inactiveRooms),
            'total_seats' => $totalSeats,
            'total_booked' => $totalBooked,
            'total_available' => $totalAvailable,
            'total_broken' => $totalBroken,
            'occupancy_rate' => $totalSeats > 0 
                ? round(($totalBooked / $totalSeats) * 100, 2) 
                : 0
        ];
    }

    /**
     * ✅ Xóa Cache khi có thay đổi dữ liệu
     */
    public function clearCache($roomId = null)
    {
        Cache::forget('room_management_dashboard');
        Cache::forget('room_management_statistics');
        
        // ✅ Xóa cache chi tiết phòng nếu có ID
        if ($roomId) {
            Cache::forget("room_management_detail_{$roomId}");
        }
    }
}