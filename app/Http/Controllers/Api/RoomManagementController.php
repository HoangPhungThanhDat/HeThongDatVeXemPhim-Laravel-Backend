<?php
// app/Http/Controllers/Api/RoomManagementController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomManagementController extends Controller
{
    /**
     * 📊 API tổng hợp: Lấy tất cả dữ liệu cho trang quản lý phòng chiếu
     * Bao gồm: Danh sách rạp, phòng chiếu, thống kê ghế, suất chiếu hiện tại
     * 
     * GET: /api/admin/room-management/dashboard
     */
    public function getDashboardData(Request $request)
    {
        try {
            // 1️⃣ Lấy danh sách rạp đang hoạt động
            $cinemas = Cinema::where('Status', 'Active')
                ->orderBy('Name')
                ->get(['CinemaId', 'Name', 'Address', 'City', 'Phone', 'Email', 'ImageUrl', 'Status']);

            if ($cinemas->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Không có rạp chiếu nào',
                    'data' => [],
                    'meta' => [
                        'total_cinemas' => 0,
                        'total_rooms' => 0,
                        'fetched_at' => now()->toDateTimeString()
                    ]
                ]);
            }

            // 2️⃣ Lấy danh sách phòng chiếu (kèm thông tin rạp)
            $rooms = Room::with(['cinema' => function($q) {
                $q->select('CinemaId', 'Name');
            }])
            ->select('RoomId', 'CinemaId', 'Name', 'RoomType', 'SeatCount', 'Status', 
                     'CreatedAt', 'UpdatedAt', 'CreatedBy', 'UpdatedBy')
            ->get();

            // 3️⃣ Lấy thống kê ghế cho từng phòng
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

            // 4️⃣ Lấy suất chiếu đang diễn ra (cho từng phòng)
            $now = now();
            $currentShowtimes = Showtime::where('StartTime', '<=', $now)
                ->where('EndTime', '>=', $now)
                ->where('Status', 'Scheduled')
                ->with(['movie' => function($q) {
                    $q->select('MovieId', 'Title');
                }])
                ->get(['ShowtimeId', 'RoomId', 'MovieId', 'StartTime', 'EndTime'])
                ->groupBy('RoomId');

            // 5️⃣ Tổng hợp dữ liệu
            $result = [];
            foreach ($cinemas as $cinema) {
                $cinemaRooms = $rooms->where('CinemaId', $cinema->CinemaId);
                
                $roomData = [];
                foreach ($cinemaRooms as $room) {
                    $stats = $seatStats->get($room->RoomId);
                    
                    // Lấy suất chiếu hiện tại
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

                // Tính thống kê cho rạp
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

            return response()->json([
                'success' => true,
                'message' => 'Lấy dữ liệu thành công',
                'data' => $result,
                'meta' => [
                    'total_cinemas' => $cinemas->count(),
                    'total_rooms' => $rooms->count(),
                    'fetched_at' => now()->toDateTimeString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('RoomManagementController::getDashboardData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu quản lý phòng chiếu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🎯 API lấy chi tiết phòng + sơ đồ ghế
     * Gọi khi user bấm "Xem chi tiết"
     * 
     * GET: /api/admin/room-management/rooms/{roomId}/detail
     */
    public function getRoomDetail($roomId)
    {
        try {
            // 1️⃣ Lấy thông tin phòng
            $room = Room::with(['cinema' => function($q) {
                $q->select('CinemaId', 'Name', 'Address');
            }])
            ->findOrFail($roomId);

            // 2️⃣ Lấy danh sách ghế của phòng (sơ đồ ghế)
            $seats = Seat::where('RoomId', $roomId)
                ->orderBy('Row')
                ->orderBy('Number')
                ->get(['SeatId', 'Row', 'Number', 'SeatType', 'Status']);

            // 3️⃣ Lấy thống kê ghế
            $seatStats = [
                'total' => $seats->count(),
                'available' => $seats->where('Status', 'Available')->count(),
                'booked' => $seats->whereIn('Status', ['Booked', 'Reserved'])->count(),
                'broken' => $seats->where('Status', 'Broken')->count(),
                'inactive' => $seats->where('Status', 'Inactive')->count(),
            ];

            // 4️⃣ Lấy suất chiếu đang diễn ra
            $now = now();
            $currentShowtime = Showtime::where('RoomId', $roomId)
                ->where('StartTime', '<=', $now)
                ->where('EndTime', '>=', $now)
                ->where('Status', 'Scheduled')
                ->with(['movie' => function($q) {
                    $q->select('MovieId', 'Title', 'PosterUrl');
                }])
                ->first();

            // 5️⃣ Nhóm ghế theo hàng để hiển thị sơ đồ
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

            // Sắp xếp hàng ghế theo thứ tự A, B, C...
            ksort($seatsByRow);

            return response()->json([
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
            ]);

        } catch (\Exception $e) {
            Log::error('RoomManagementController::getRoomDetail', [
                'roomId' => $roomId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể tải chi tiết phòng chiếu',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📊 API lấy thống kê nhanh cho dashboard
     * 
     * GET: /api/admin/room-management/statistics
     */
    public function getStatistics(Request $request)
    {
        try {
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

            return response()->json([
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
            ]);

        } catch (\Exception $e) {
            Log::error('RoomManagementController::getStatistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể tải thống kê',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🪑 API cập nhật sơ đồ ghế của phòng
     * 
     * PUT: /api/admin/room-management/rooms/{roomId}/seats
     */
    public function updateSeats(Request $request, $roomId)
    {
        try {
            $request->validate([
                'seats' => 'required|array',
                'seats.*.SeatId' => 'required|exists:seats,SeatId',
                'seats.*.Row' => 'required|string|max:1',
                'seats.*.Number' => 'required|integer|min:1',
                'seats.*.SeatType' => 'required|in:Normal,VIP,Couple',
                'seats.*.Status' => 'required|in:Available,Booked,Reserved,Broken,Inactive',
            ]);

            DB::beginTransaction();

            foreach ($request->seats as $seatData) {
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

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật sơ đồ ghế thành công',
                'updated_count' => count($request->seats)
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RoomManagementController::updateSeats', [
                'roomId' => $roomId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật sơ đồ ghế',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔄 Tính toán thống kê cho rạp
     */
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
}