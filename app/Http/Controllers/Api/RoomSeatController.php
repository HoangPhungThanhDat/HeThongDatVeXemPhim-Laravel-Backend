<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomSeatController extends Controller
{
    /**
     * Lấy danh sách rạp chiếu
     * GET /api/room-seat/cinemas
     */
    public function getCinemas()
    {
        try {
            $cinemas = Cinema::where('Status', 'Active')
                ->select('CinemaId as id', 'Name as name', 'Address as address', 'City as city')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $cinemas
            ]);
        } catch (\Exception $e) {
            Log::error('RoomSeatController@getCinemas error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách phòng theo rạp
     * GET /api/room-seat/rooms?cinema_id={cinemaId}
     */
    public function getRooms(Request $request)
    {
        try {
            $query = Room::where('Status', 'Active');

            if ($request->cinema_id) {
                $query->where('CinemaId', $request->cinema_id);
            }

            $rooms = $query->select(
                'RoomId as id',
                'Name as name',
                'CinemaId',
                'RoomType',
                'SeatCount',
                'Status'
            )->get();

            // Lấy thống kê ghế cho từng phòng
            $roomsWithStats = $rooms->map(function($room) {
                $seatStats = Seat::where('RoomId', $room->id)
                    ->selectRaw('
                        COUNT(*) as total,
                        SUM(CASE WHEN Status = "Available" THEN 1 ELSE 0 END) as available,
                        SUM(CASE WHEN Status = "Booked" THEN 1 ELSE 0 END) as booked,
                        SUM(CASE WHEN Status = "Broken" THEN 1 ELSE 0 END) as broken,
                        SUM(CASE WHEN Status = "Inactive" THEN 1 ELSE 0 END) as inactive
                    ')
                    ->first();

                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'cinema_id' => $room->CinemaId,
                    'room_type' => $room->RoomType,
                    'seat_count' => $room->SeatCount,
                    'status' => $room->Status,
                    'stats' => [
                        'total' => $seatStats->total ?? 0,
                        'available' => $seatStats->available ?? 0,
                        'booked' => $seatStats->booked ?? 0,
                        'broken' => $seatStats->broken ?? 0,
                        'inactive' => $seatStats->inactive ?? 0,
                    ]
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $roomsWithStats
            ]);
        } catch (\Exception $e) {
            Log::error('RoomSeatController@getRooms error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy sơ đồ ghế của phòng
     * GET /api/room-seat/room/{roomId}/seats
     */
    public function getSeats($roomId)
    {
        try {
            $room = Room::find($roomId);
            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy phòng chiếu'
                ], 404);
            }

            $seats = Seat::where('RoomId', $roomId)
                ->orderBy('Row')
                ->orderBy('Number')
                ->get()
                ->map(function($seat) {
                    return [
                        'id' => $seat->SeatId,
                        'room_id' => $seat->RoomId,
                        'row' => $seat->Row,
                        'number' => $seat->Number,
                        'seat_type' => $seat->SeatType ?? 'Normal',
                        'status' => $seat->Status ?? 'Available',
                        'label' => $seat->Row . $seat->Number,
                    ];
                });

            // Nhóm ghế theo hàng
            $groupedSeats = [];
            foreach ($seats as $seat) {
                $row = $seat['row'];
                if (!isset($groupedSeats[$row])) {
                    $groupedSeats[$row] = [];
                }
                $groupedSeats[$row][] = $seat;
            }

            // Sắp xếp hàng
            ksort($groupedSeats);

            // Sắp xếp ghế trong mỗi hàng
            foreach ($groupedSeats as &$rowSeats) {
                usort($rowSeats, function($a, $b) {
                    return $a['number'] - $b['number'];
                });
            }

            // Thống kê
            $stats = [
                'total' => $seats->count(),
                'available' => $seats->where('status', 'Available')->count(),
                'booked' => $seats->where('status', 'Booked')->count(),
                'broken' => $seats->where('status', 'Broken')->count(),
                'inactive' => $seats->where('status', 'Inactive')->count(),
            ];

            // Lấy danh sách hàng và cột
            $rows = $seats->pluck('row')->unique()->sort()->values();
            $cols = $seats->pluck('number')->unique()->sort()->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'room' => [
                        'id' => $room->RoomId,
                        'name' => $room->Name,
                        'room_type' => $room->RoomType,
                        'seat_count' => $room->SeatCount,
                        'status' => $room->Status,
                    ],
                    'seats' => $seats,
                    'grouped_seats' => $groupedSeats,
                    'stats' => $stats,
                    'rows' => $rows,
                    'cols' => $cols,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('RoomSeatController@getSeats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Thêm hàng ghế mới
     * POST /api/room-seat/room/{roomId}/add-rows
     */
    public function addRows(Request $request, $roomId)
    {
        $request->validate([
            'row_count' => 'required|integer|min:1|max:10',
            'position' => 'nullable|in:start,end',
        ]);

        try {
            DB::beginTransaction();

            $position = $request->position ?? 'end';
            $rowCount = $request->row_count;

            // Lấy danh sách hàng hiện có
            $existingRows = Seat::where('RoomId', $roomId)
                ->select('Row')
                ->distinct()
                ->pluck('Row')
                ->toArray();

            // Danh sách hàng từ A-Z
            $allRows = range('A', 'Z');
            
            // Tìm hàng mới
            $newRows = [];
            if ($position === 'start') {
                foreach ($allRows as $row) {
                    if (!in_array($row, $existingRows) && count($newRows) < $rowCount) {
                        $newRows[] = $row;
                    }
                }
            } else {
                // Tìm hàng cuối cùng
                $lastRow = !empty($existingRows) ? max($existingRows) : 'A';
                $startIndex = array_search($lastRow, $allRows) + 1;
                
                for ($i = $startIndex; $i < count($allRows) && count($newRows) < $rowCount; $i++) {
                    $newRows[] = $allRows[$i];
                }
            }

            if (empty($newRows)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có hàng mới để thêm (đã đạt giới hạn A-Z)'
                ], 400);
            }

            // Lấy số cột hiện tại
            $maxCol = Seat::where('RoomId', $roomId)->max('Number') ?? 0;
            $colCount = $maxCol > 0 ? $maxCol : 1;

            $created = 0;
            foreach ($newRows as $row) {
                for ($col = 1; $col <= $colCount; $col++) {
                    // Kiểm tra ghế đã tồn tại
                    $exists = Seat::where('RoomId', $roomId)
                        ->where('Row', $row)
                        ->where('Number', $col)
                        ->exists();

                    if (!$exists) {
                        Seat::create([
                            'RoomId' => $roomId,
                            'Row' => $row,
                            'Number' => $col,
                            'SeatType' => 'Normal',
                            'Status' => 'Available',
                            'CreatedAt' => now(),
                            'UpdatedAt' => now(),
                        ]);
                        $created++;
                    }
                }
            }

            // Cập nhật SeatCount trong Room
            $totalSeats = Seat::where('RoomId', $roomId)->count();
            Room::where('RoomId', $roomId)->update(['SeatCount' => $totalSeats]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã thêm {$created} ghế (". count($newRows) ." hàng × {$colCount} cột)",
                'data' => [
                    'rows_added' => $newRows,
                    'total_seats_added' => $created,
                    'new_seat_count' => $totalSeats,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RoomSeatController@addRows error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Thêm cột ghế mới
     * POST /api/room-seat/room/{roomId}/add-cols
     */
    public function addCols(Request $request, $roomId)
    {
        $request->validate([
            'col_count' => 'required|integer|min:1|max:20',
            'position' => 'nullable|in:start,end',
        ]);

        try {
            DB::beginTransaction();

            $position = $request->position ?? 'end';
            $colCount = $request->col_count;

            // Lấy danh sách cột hiện có
            $existingCols = Seat::where('RoomId', $roomId)
                ->select('Number')
                ->distinct()
                ->pluck('Number')
                ->toArray();

            // Tìm cột mới
            $newCols = [];
            if ($position === 'start') {
                $maxCol = !empty($existingCols) ? min($existingCols) : 1;
                for ($i = $maxCol - 1; $i >= 1 && count($newCols) < $colCount; $i--) {
                    if (!in_array($i, $existingCols)) {
                        $newCols[] = $i;
                    }
                }
                sort($newCols);
            } else {
                $maxCol = !empty($existingCols) ? max($existingCols) : 0;
                for ($i = $maxCol + 1; $i <= $maxCol + $colCount; $i++) {
                    $newCols[] = $i;
                }
            }

            if (empty($newCols)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có cột mới để thêm'
                ], 400);
            }

            // Lấy danh sách hàng hiện có
            $existingRows = Seat::where('RoomId', $roomId)
                ->select('Row')
                ->distinct()
                ->pluck('Row')
                ->toArray();

            if (empty($existingRows)) {
                // Nếu chưa có hàng, tạo hàng A
                $existingRows = ['A'];
            }

            $created = 0;
            foreach ($existingRows as $row) {
                foreach ($newCols as $col) {
                    $exists = Seat::where('RoomId', $roomId)
                        ->where('Row', $row)
                        ->where('Number', $col)
                        ->exists();

                    if (!$exists) {
                        Seat::create([
                            'RoomId' => $roomId,
                            'Row' => $row,
                            'Number' => $col,
                            'SeatType' => 'Normal',
                            'Status' => 'Available',
                            'CreatedAt' => now(),
                            'UpdatedAt' => now(),
                        ]);
                        $created++;
                    }
                }
            }

            // Cập nhật SeatCount trong Room
            $totalSeats = Seat::where('RoomId', $roomId)->count();
            Room::where('RoomId', $roomId)->update(['SeatCount' => $totalSeats]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã thêm {$created} ghế (". count($existingRows) ." hàng × ". count($newCols) ." cột)",
                'data' => [
                    'cols_added' => $newCols,
                    'total_seats_added' => $created,
                    'new_seat_count' => $totalSeats,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RoomSeatController@addCols error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật ghế (loại ghế, trạng thái)
     * PUT /api/room-seat/seat/{seatId}
     */
    public function updateSeat(Request $request, $seatId)
    {
        $request->validate([
            'seat_type' => 'nullable|string|in:Normal,VIP,Couple,Accessible,Sweetbox',
            'status' => 'nullable|string|in:Available,Booked,Broken,Inactive',
        ]);

        try {
            $seat = Seat::find($seatId);
            if (!$seat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy ghế'
                ], 404);
            }

            if ($request->has('seat_type')) {
                $seat->SeatType = $request->seat_type;
            }
            if ($request->has('status')) {
                $seat->Status = $request->status;
            }
            $seat->UpdatedAt = now();
            $seat->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật ghế thành công',
                'data' => [
                    'id' => $seat->SeatId,
                    'row' => $seat->Row,
                    'number' => $seat->Number,
                    'seat_type' => $seat->SeatType,
                    'status' => $seat->Status,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('RoomSeatController@updateSeat error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa hàng ghế
     * DELETE /api/room-seat/room/{roomId}/row/{row}
     */
    public function deleteRow($roomId, $row)
    {
        try {
            DB::beginTransaction();

            $deleted = Seat::where('RoomId', $roomId)
                ->where('Row', $row)
                ->delete();

            if ($deleted === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy hàng ghế để xóa'
                ], 404);
            }

            // Cập nhật SeatCount trong Room
            $totalSeats = Seat::where('RoomId', $roomId)->count();
            Room::where('RoomId', $roomId)->update(['SeatCount' => $totalSeats]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã xóa hàng {$row} với {$deleted} ghế",
                'data' => [
                    'row' => $row,
                    'deleted_count' => $deleted,
                    'remaining_seats' => $totalSeats,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RoomSeatController@deleteRow error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa cột ghế
     * DELETE /api/room-seat/room/{roomId}/col/{col}
     */
    public function deleteCol($roomId, $col)
    {
        try {
            DB::beginTransaction();

            $deleted = Seat::where('RoomId', $roomId)
                ->where('Number', $col)
                ->delete();

            if ($deleted === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy cột ghế để xóa'
                ], 404);
            }

            // Cập nhật SeatCount trong Room
            $totalSeats = Seat::where('RoomId', $roomId)->count();
            Room::where('RoomId', $roomId)->update(['SeatCount' => $totalSeats]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã xóa cột {$col} với {$deleted} ghế",
                'data' => [
                    'col' => $col,
                    'deleted_count' => $deleted,
                    'remaining_seats' => $totalSeats,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RoomSeatController@deleteCol error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa 1 ghế
     * DELETE /api/room-seat/seat/{seatId}
     */
    public function deleteSeat($seatId)
    {
        try {
            $seat = Seat::find($seatId);
            if (!$seat) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy ghế'
                ], 404);
            }

            $row = $seat->Row;
            $number = $seat->Number;
            $roomId = $seat->RoomId;

            $seat->delete();

            // Cập nhật SeatCount trong Room
            $totalSeats = Seat::where('RoomId', $roomId)->count();
            Room::where('RoomId', $roomId)->update(['SeatCount' => $totalSeats]);

            return response()->json([
                'success' => true,
                'message' => "Đã xóa ghế {$row}{$number}",
                'data' => [
                    'seat_id' => $seatId,
                    'row' => $row,
                    'number' => $number,
                    'remaining_seats' => $totalSeats,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('RoomSeatController@deleteSeat error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset tất cả ghế về trạng thái Available
     * POST /api/room-seat/room/{roomId}/reset
     */
    public function resetSeats($roomId)
    {
        try {
            DB::beginTransaction();

            $updated = Seat::where('RoomId', $roomId)
                ->where('Status', '!=', 'Available')
                ->update([
                    'Status' => 'Available',
                    'UpdatedAt' => now(),
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã reset {$updated} ghế về trạng thái trống",
                'data' => [
                    'reset_count' => $updated,
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('RoomSeatController@resetSeats error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}