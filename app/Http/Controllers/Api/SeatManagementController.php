<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Room;
use App\Models\Showtime;
use App\Models\ShowtimeSeat;
use App\Models\Seat;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SeatManagementController extends Controller
{
    /**
     * Lấy toàn bộ dữ liệu cho trang quản lý ghế
     * GET /api/seat-management/data
     */
    public function getData(Request $request)
    {
        try {
            // 1. Lấy danh sách rạp
            $cinemas = Cinema::where('Status', 'Active')
                ->select('CinemaId as id', 'Name as name', 'City as city')
                ->get();

            // 2. Lấy danh sách phòng theo rạp (nếu có filter)
            $roomsQuery = Room::where('Status', 'Active');
            if ($request->cinema_id) {
                $roomsQuery->where('CinemaId', $request->cinema_id);
            }
            $rooms = $roomsQuery->select('RoomId as id', 'Name as name', 'CinemaId', 'RoomType', 'SeatCount')
                ->get();

            // 3. Lấy danh sách suất chiếu theo phòng (nếu có filter)
            $showtimesQuery = Showtime::with(['movie', 'room'])
                ->where('Status', 'Scheduled');
            
            if ($request->room_id) {
                $showtimesQuery->where('RoomId', $request->room_id);
            }
            if ($request->cinema_id) {
                $showtimesQuery->whereHas('room', function($q) use ($request) {
                    $q->where('CinemaId', $request->cinema_id);
                });
            }
            
            $showtimes = $showtimesQuery->get()->map(function($st) {
                return [
                    'id' => $st->ShowtimeId,
                    'room_id' => $st->RoomId,
                    'movie_title' => $st->movie->Title ?? 'Unknown',
                    'room_name' => $st->room->Name ?? 'Unknown',
                    'start_time' => $st->StartTime,
                    'end_time' => $st->EndTime,
                    'price' => $st->Price,
                    'status' => $st->Status,
                ];
            });

            // 4. Lấy danh sách ghế theo suất chiếu (nếu có)
            $seats = [];
            $roomInfo = null;
            $stats = [
                'total' => 0,
                'trong' => 0,
                'dat' => 0,
                'giu' => 0,
                'loi' => 0,
                'baoTri' => 0,
                'room_total' => 0,
            ];

            if ($request->showtime_id) {
                // ✅ Lấy thông tin suất chiếu và phòng
                $showtime = Showtime::with('room')->find($request->showtime_id);
                
                if ($showtime && $showtime->room) {
                    $roomInfo = [
                        'id' => $showtime->room->RoomId,
                        'name' => $showtime->room->Name,
                        'room_type' => $showtime->room->RoomType,
                        'seat_count' => $showtime->room->SeatCount ?? 0,
                        'status' => $showtime->room->Status,
                    ];
                    $stats['room_total'] = $showtime->room->SeatCount ?? 0;
                    
                    // ✅ Lấy ghế từ ShowtimeSeat
                    $showtimeSeats = ShowtimeSeat::with(['seat'])
                        ->where('ShowtimeId', $request->showtime_id)
                        ->get();
                    
                    // ✅ Nếu chưa có ghế nào, tự động tạo từ ghế của phòng
                    if ($showtimeSeats->isEmpty()) {
                        // Lấy tất cả ghế của phòng
                        $roomSeats = Seat::where('RoomId', $showtime->room->RoomId)->get();
                        
                        if ($roomSeats->isNotEmpty()) {
                            // Tạo ShowtimeSeat cho từng ghế
                            foreach ($roomSeats as $roomSeat) {
                                ShowtimeSeat::create([
                                    'ShowtimeId' => $request->showtime_id,
                                    'SeatId' => $roomSeat->SeatId,
                                    'Status' => 'Available',
                                    'CreatedAt' => now(),
                                    'UpdatedAt' => now(),
                                ]);
                            }
                            
                            // Lấy lại danh sách ghế sau khi tạo
                            $showtimeSeats = ShowtimeSeat::with(['seat'])
                                ->where('ShowtimeId', $request->showtime_id)
                                ->get();
                        }
                    }
                    
                    // ✅ Map dữ liệu ghế
                    $seats = $showtimeSeats->map(function($ss) {
                        $ticket = null;
                        try {
                            $ticket = Ticket::where('SeatId', $ss->SeatId)
                                ->where('ShowtimeId', $ss->ShowtimeId)
                                ->whereIn('Status', ['Paid', 'Pending'])
                                ->first();
                        } catch (\Exception $e) {
                            // Bỏ qua lỗi ticket
                        }
                        
                        $user = null;
                        if ($ticket) {
                            try {
                                $user = $ticket->user;
                            } catch (\Exception $e) {
                                // Bỏ qua lỗi user
                            }
                        }
                        
                        $seatType = $ss->seat->SeatType ?? 'Normal';
                        $seatTypeLower = strtolower($seatType);
                        
                        return [
                            'id' => $ss->ShowtimeSeatId,
                            'showtime_id' => $ss->ShowtimeId,
                            'seat_id' => $ss->SeatId,
                            'row' => $ss->seat->Row ?? 'A',
                            'number' => $ss->seat->Number ?? 0,
                            'seat_type' => $seatTypeLower,
                            'seat_type_raw' => $seatType,
                            'status' => $this->mapStatusToLocal($ss->Status),
                            'status_raw' => $ss->Status,
                            'ticket_code' => $ticket ? ($ticket->TicketCode ?? null) : null,
                            'customer_name' => $user ? ($user->FullName ?? null) : null,
                            'created_at' => $ss->CreatedAt,
                            'updated_at' => $ss->UpdatedAt,
                        ];
                    });
                }
            }

            // 5. Thống kê ghế
            $stats['total'] = count($seats);
            foreach ($seats as $seat) {
                if (isset($stats[$seat['status']])) {
                    $stats[$seat['status']]++;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'cinemas' => $cinemas,
                    'rooms' => $rooms,
                    'showtimes' => $showtimes,
                    'seats' => $seats,
                    'stats' => $stats,
                    'room' => $roomInfo,
                    'filters' => [
                        'cinema_id' => $request->cinema_id,
                        'room_id' => $request->room_id,
                        'showtime_id' => $request->showtime_id,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('SeatManagementController@getData error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách ghế theo suất chiếu
     * GET /api/seat-management/showtime/{showtimeId}/seats
     */
    public function getSeatsByShowtime($showtimeId, Request $request)
    {
        try {
            // ✅ Kiểm tra và tạo ghế nếu chưa có
            $showtime = Showtime::with('room')->find($showtimeId);
            if ($showtime && $showtime->room) {
                $existingSeats = ShowtimeSeat::where('ShowtimeId', $showtimeId)->count();
                if ($existingSeats == 0) {
                    // Lấy tất cả ghế của phòng
                    $roomSeats = Seat::where('RoomId', $showtime->room->RoomId)->get();
                    if ($roomSeats->isNotEmpty()) {
                        foreach ($roomSeats as $roomSeat) {
                            ShowtimeSeat::create([
                                'ShowtimeId' => $showtimeId,
                                'SeatId' => $roomSeat->SeatId,
                                'Status' => 'Available',
                                'CreatedAt' => now(),
                                'UpdatedAt' => now(),
                            ]);
                        }
                    }
                }
            }

            $query = ShowtimeSeat::with(['seat'])
                ->where('ShowtimeId', $showtimeId);

            $perPage = $request->per_page ?? 100;
            $page = $request->page ?? 1;

            $showtimeSeats = $query->paginate($perPage, ['*'], 'page', $page);

            $seats = $showtimeSeats->map(function($ss) {
                $ticket = null;
                try {
                    $ticket = Ticket::where('SeatId', $ss->SeatId)
                        ->where('ShowtimeId', $ss->ShowtimeId)
                        ->whereIn('Status', ['Paid', 'Pending'])
                        ->first();
                } catch (\Exception $e) {
                    // Bỏ qua lỗi ticket
                }
                
                $user = null;
                if ($ticket) {
                    try {
                        $user = $ticket->user;
                    } catch (\Exception $e) {
                        // Bỏ qua lỗi user
                    }
                }
                
                $seatType = $ss->seat->SeatType ?? 'Normal';
                $seatTypeLower = strtolower($seatType);
                
                return [
                    'id' => $ss->ShowtimeSeatId,
                    'showtime_id' => $ss->ShowtimeId,
                    'seat_id' => $ss->SeatId,
                    'row' => $ss->seat->Row ?? 'A',
                    'number' => $ss->seat->Number ?? 0,
                    'seat_type' => $seatTypeLower,
                    'seat_type_raw' => $seatType,
                    'status' => $this->mapStatusToLocal($ss->Status),
                    'status_raw' => $ss->Status,
                    'ticket_code' => $ticket ? ($ticket->TicketCode ?? null) : null,
                    'customer_name' => $user ? ($user->FullName ?? null) : null,
                ];
            });

            // Thống kê
            $stats = ['trong' => 0, 'dat' => 0, 'giu' => 0, 'loi' => 0, 'baoTri' => 0];
            $allSeats = ShowtimeSeat::where('ShowtimeId', $showtimeId)->get();
            foreach ($allSeats as $ss) {
                $status = $this->mapStatusToLocal($ss->Status);
                if (isset($stats[$status])) {
                    $stats[$status]++;
                }
            }

            // ✅ Thêm thông tin tổng ghế của phòng
            $roomTotal = 0;
            if ($showtime && $showtime->room) {
                $roomTotal = $showtime->room->SeatCount ?? 0;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'seats' => $seats,
                    'stats' => $stats,
                    'total' => $showtimeSeats->total(),
                    'room_total' => $roomTotal,
                    'current_page' => $showtimeSeats->currentPage(),
                    'per_page' => $showtimeSeats->perPage(),
                    'last_page' => $showtimeSeats->lastPage(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('SeatManagementController@getSeatsByShowtime error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo ghế tự động cho suất chiếu
     * POST /api/seat-management/generate
     * ✅ SỬA: Tạo đúng số ghế của phòng
     */
    public function generateSeats(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|exists:showtimes,ShowtimeId'
        ]);

        try {
            DB::beginTransaction();

            $showtimeId = $request->showtime_id;
            $showtime = Showtime::with('room')->find($showtimeId);
            
            if (!$showtime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy suất chiếu'
                ], 404);
            }

            $room = $showtime->room;
            
            // ✅ Lấy tất cả ghế của phòng từ bảng Seats
            $roomSeats = Seat::where('RoomId', $room->RoomId)->get();
            
            // Nếu phòng chưa có ghế nào, tạo ghế mặc định
            if ($roomSeats->isEmpty()) {
                $seatCount = $room->SeatCount ?? 50;
                $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
                $seatsPerRow = ceil($seatCount / count($rows));
                $createdSeats = [];
                
                for ($i = 0; $i < count($rows) && count($createdSeats) < $seatCount; $i++) {
                    $row = $rows[$i];
                    for ($j = 1; $j <= $seatsPerRow && count($createdSeats) < $seatCount; $j++) {
                        $seat = Seat::create([
                            'RoomId' => $room->RoomId,
                            'Row' => $row,
                            'Number' => $j,
                            'SeatType' => $j % 5 == 0 ? 'VIP' : 'Normal',
                            'Status' => 'Available',
                            'CreatedAt' => now(),
                            'UpdatedAt' => now(),
                        ]);
                        $createdSeats[] = $seat;
                    }
                }
                $roomSeats = collect($createdSeats);
                
                // Cập nhật SeatCount trong Room
                $room->SeatCount = count($createdSeats);
                $room->save();
            }
            
            // ✅ Xóa ghế cũ của suất chiếu
            $deleted = ShowtimeSeat::where('ShowtimeId', $showtimeId)->delete();
            
            // ✅ Tạo ShowtimeSeat cho TẤT CẢ ghế của phòng
            $created = 0;
            foreach ($roomSeats as $seat) {
                ShowtimeSeat::create([
                    'ShowtimeId' => $showtimeId,
                    'SeatId' => $seat->SeatId,
                    'Status' => 'Available',
                    'CreatedAt' => now(),
                    'UpdatedAt' => now(),
                ]);
                $created++;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã tạo {$created} ghế cho suất chiếu",
                'data' => [
                    'total_seats' => $created,
                    'deleted_old_seats' => $deleted,
                    'room_seats' => $roomSeats->count(),
                    'room_name' => $room->Name,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('SeatManagementController@generateSeats error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi tạo ghế: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật trạng thái ghế
     * PUT /api/seat-management/seat
     */
    public function updateSeat(Request $request)
    {
        $request->validate([
            'showtime_seat_id' => 'required|exists:showtimeseats,ShowtimeSeatId',
            'status' => 'required|in:Available,Reserved,Held,Broken,Inactive'
        ]);

        try {
            $showtimeSeat = ShowtimeSeat::find($request->showtime_seat_id);
            
            if (!$showtimeSeat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy ghế'
                ], 404);
            }

            $oldStatus = $showtimeSeat->Status;
            $showtimeSeat->Status = $request->status;
            $showtimeSeat->UpdatedAt = now();
            $showtimeSeat->UpdatedBy = auth()->user()->UserId ?? null;
            $showtimeSeat->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công',
                'data' => [
                    'id' => $showtimeSeat->ShowtimeSeatId,
                    'old_status' => $oldStatus,
                    'new_status' => $request->status,
                    'status' => $this->mapStatusToLocal($showtimeSeat->Status),
                    'status_raw' => $showtimeSeat->Status,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('SeatManagementController@updateSeat error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi cập nhật: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset ghế về trạng thái Available
     * POST /api/seat-management/reset
     */
    public function resetSeat(Request $request)
    {
        $request->validate([
            'showtime_seat_id' => 'required|exists:showtimeseats,ShowtimeSeatId'
        ]);

        try {
            $showtimeSeat = ShowtimeSeat::find($request->showtime_seat_id);
            
            if (!$showtimeSeat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy ghế'
                ], 404);
            }

            $oldStatus = $showtimeSeat->Status;
            $showtimeSeat->Status = 'Available';
            $showtimeSeat->UpdatedAt = now();
            $showtimeSeat->UpdatedBy = auth()->user()->UserId ?? null;
            $showtimeSeat->save();

            return response()->json([
                'success' => true,
                'message' => 'Đã reset ghế về trạng thái trống',
                'data' => [
                    'id' => $showtimeSeat->ShowtimeSeatId,
                    'old_status' => $oldStatus,
                    'new_status' => 'Available',
                    'status' => 'trong',
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('SeatManagementController@resetSeat error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi reset: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa ghế
     * DELETE /api/seat-management/seat
     */
    public function deleteSeat(Request $request)
    {
        $request->validate([
            'showtime_seat_id' => 'required|exists:showtimeseats,ShowtimeSeatId'
        ]);

        try {
            $showtimeSeat = ShowtimeSeat::find($request->showtime_seat_id);
            
            if (!$showtimeSeat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy ghế để xóa'
                ], 404);
            }

            $deleted = $showtimeSeat->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đã xóa ghế thành công',
                'data' => [
                    'id' => $request->showtime_seat_id,
                    'deleted' => true
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('SeatManagementController@deleteSeat error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xóa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa tất cả ghế của suất chiếu
     * DELETE /api/seat-management/showtime/{showtimeId}/seats
     */
    public function deleteAllSeatsByShowtime($showtimeId)
    {
        try {
            $deleted = ShowtimeSeat::where('ShowtimeId', $showtimeId)->delete();

            return response()->json([
                'success' => true,
                'message' => "Đã xóa {$deleted} ghế của suất chiếu",
                'data' => [
                    'showtime_id' => $showtimeId,
                    'deleted_count' => $deleted
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('SeatManagementController@deleteAllSeatsByShowtime error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Lỗi xóa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Map status từ DB sang tiếng Việt
     */
    private function mapStatusToLocal($status)
    {
        $map = [
            'Available' => 'trong',
            'Reserved' => 'dat',
            'Held' => 'giu',
            'Broken' => 'loi',
            'Inactive' => 'baoTri'
        ];
        return $map[$status] ?? 'trong';
    }

    /**
     * Map status từ tiếng Việt sang DB
     */
    private function mapStatusToDb($status)
    {
        $map = [
            'trong' => 'Available',
            'dat' => 'Reserved',
            'giu' => 'Held',
            'loi' => 'Broken',
            'baoTri' => 'Inactive'
        ];
        return $map[$status] ?? 'Available';
    }
}