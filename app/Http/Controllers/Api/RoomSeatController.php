<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cinema;
use App\Models\Room;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoomSeatController extends Controller
{
    public function getCinemas()
    {
        try {
            $cinemas = Cinema::where('Status', 'Active')
                ->select('CinemaId as id', 'Name as name', 'Address as address', 'City as city')
                ->get();
            return response()->json(['success' => true, 'data' => $cinemas]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getRooms(Request $request)
    {
        try {
            $query = Room::where('Status', 'Active');
            if ($request->cinema_id) $query->where('CinemaId', $request->cinema_id);

            $rooms = $query->select('RoomId as id', 'Name as name', 'CinemaId', 'RoomType', 'SeatCount', 'Status')->get();
            $roomIds = $rooms->pluck('id')->toArray();

            $stats = [];
            if (!empty($roomIds)) {
                $seatStats = Seat::whereIn('RoomId', $roomIds)
                    ->selectRaw('RoomId, COUNT(*) as total, SUM(CASE WHEN Status = "Available" THEN 1 ELSE 0 END) as available')
                    ->groupBy('RoomId')->get()->keyBy('RoomId');

                foreach ($seatStats as $stat) {
                    $stats[$stat->RoomId] = ['total' => $stat->total ?? 0, 'available' => $stat->available ?? 0];
                }
            }

            $roomsWithStats = $rooms->map(function($room) use ($stats) {
                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'cinema_id' => $room->CinemaId,
                    'room_type' => $room->RoomType,
                    'seat_count' => $room->SeatCount,
                    'status' => $room->Status,
                    'stats' => $stats[$room->id] ?? ['total' => 0, 'available' => 0]
                ];
            });

            return response()->json(['success' => true, 'data' => $roomsWithStats]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getSeats($roomId)
    {
        try {
            $room = Room::find($roomId);
            if (!$room) return response()->json(['success' => false, 'message' => 'Không tìm thấy phòng'], 404);

            $seats = Seat::where('RoomId', $roomId)->orderBy('Row')->orderBy('Number')->get()
                ->map(function($seat) {
                    return [
                        'id' => $seat->SeatId, 'room_id' => $seat->RoomId, 'row' => $seat->Row,
                        'number' => $seat->Number, 'seat_type' => $seat->SeatType ?? 'Normal',
                        'status' => $seat->Status ?? 'Available', 'label' => $seat->Row . $seat->Number,
                    ];
                });

            $groupedSeats = [];
            foreach ($seats as $seat) { $groupedSeats[$seat['row']][] = $seat; }
            ksort($groupedSeats);

            return response()->json([
                'success' => true,
                'data' => [
                    'room' => ['id' => $room->RoomId, 'name' => $room->Name, 'room_type' => $room->RoomType, 'seat_count' => $room->SeatCount, 'status' => $room->Status],
                    'seats' => $seats, 'grouped_seats' => $groupedSeats,
                    'rows' => $seats->pluck('row')->unique()->sort()->values(),
                    'cols' => $seats->pluck('number')->unique()->sort()->values(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * ✅ THÊM HÀNG VÀ CỘT CÙNG LÚC
     */
    public function addSeats(Request $request, $roomId)
    {
        $request->validate([
            'row_count' => 'nullable|integer|min:0|max:10',
            'col_count' => 'nullable|integer|min:0|max:20',
            'position' => 'nullable|in:start,end',
        ]);

        try {
            DB::beginTransaction();
            
            $rowCount = $request->row_count ?? 0;
            $colCount = $request->col_count ?? 0;
            $position = $request->position ?? 'end';

            if ($rowCount == 0 && $colCount == 0) {
                return response()->json(['success' => false, 'message' => 'Vui lòng chọn số hàng hoặc cột'], 400);
            }

            $existingRows = Seat::where('RoomId', $roomId)->select('Row')->distinct()->pluck('Row')->toArray();
            $existingCols = Seat::where('RoomId', $roomId)->select('Number')->distinct()->pluck('Number')->toArray();
            
            $allRows = range('A', 'Z');

            // Nếu phòng trống: Khởi tạo 1 ghế mẫu (A1) để bắt đầu
            if (empty($existingRows) && empty($existingCols)) {
                $existingRows = ['A'];
                $existingCols = [1];
                Seat::create(['RoomId' => $roomId, 'Row' => 'A', 'Number' => 1, 'SeatType' => 'Normal', 'Status' => 'Available']);
                $rowCount = max(0, $rowCount - 1);
                $colCount = max(0, $colCount - 1);
            }

            // Tính danh sách hàng mới
            $newRows = [];
            if ($rowCount > 0) {
                if ($position === 'start') {
                    foreach ($allRows as $row) {
                        if (!in_array($row, $existingRows) && count($newRows) < $rowCount) {
                            $newRows[] = $row;
                        }
                    }
                } else {
                    $lastRow = max($existingRows);
                    $startIndex = array_search($lastRow, $allRows) + 1;
                    for ($i = $startIndex; $i < count($allRows) && count($newRows) < $rowCount; $i++) {
                        $newRows[] = $allRows[$i];
                    }
                }
            }

            // Tính danh sách cột mới
            $newCols = [];
            if ($colCount > 0) {
                if ($position === 'start') {
                    $minCol = min($existingCols);
                    for ($i = $minCol - 1; $i >= 1 && count($newCols) < $colCount; $i--) {
                        if (!in_array($i, $existingCols)) $newCols[] = $i;
                    }
                    sort($newCols);
                } else {
                    $maxCol = max($existingCols);
                    for ($i = $maxCol + 1; $i <= $maxCol + $colCount; $i++) {
                        $newCols[] = $i;
                    }
                }
            }

            // Tạo ghế cho tất cả hàng và cột (không tạo trùng)
            $rowsToCreate = array_unique(array_merge($existingRows, $newRows));
            $colsToCreate = array_unique(array_merge($existingCols, $newCols));
            
            $created = 0;
            foreach ($rowsToCreate as $row) {
                foreach ($colsToCreate as $col) {
                    $exists = Seat::where('RoomId', $roomId)->where('Row', $row)->where('Number', $col)->exists();
                    if (!$exists) {
                        Seat::create([
                            'RoomId' => $roomId, 'Row' => $row, 'Number' => $col,
                            'SeatType' => 'Normal', 'Status' => 'Available',
                        ]);
                        $created++;
                    }
                }
            }

            $totalSeats = Seat::where('RoomId', $roomId)->count();
            Room::where('RoomId', $roomId)->update(['SeatCount' => $totalSeats]);
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Đã tạo thêm {$created} ghế mới",
                'data' => ['created' => $created, 'total' => $totalSeats]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

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

            $existingRows = Seat::where('RoomId', $roomId)->select('Row')->distinct()->pluck('Row')->toArray();
            $allRows = range('A', 'Z');

            if (empty($existingRows)) {
                Seat::create(['RoomId' => $roomId, 'Row' => 'A', 'Number' => 1, 'SeatType' => 'Normal', 'Status' => 'Available']);
                $existingRows = ['A'];
                $rowCount = $rowCount > 0 ? $rowCount - 1 : 0;
            }

            if ($rowCount <= 0) {
                DB::commit();
                return response()->json(['success' => true, 'message' => "Đã khởi tạo hàng A", 'data' => []]);
            }

            $newRows = [];
            if ($position === 'start') {
                foreach ($allRows as $row) {
                    if (!in_array($row, $existingRows) && count($newRows) < $rowCount) $newRows[] = $row;
                }
            } else {
                $lastRow = max($existingRows);
                $startIndex = array_search($lastRow, $allRows) + 1;
                for ($i = $startIndex; $i < count($allRows) && count($newRows) < $rowCount; $i++) $newRows[] = $allRows[$i];
            }

            if (empty($newRows)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Không có hàng mới để thêm (đã đạt giới hạn A-Z)'], 400);
            }

            $maxCol = Seat::where('RoomId', $roomId)->max('Number') ?? 1;
            $created = 0;
            foreach ($newRows as $row) {
                for ($col = 1; $col <= $maxCol; $col++) {
                    Seat::create(['RoomId' => $roomId, 'Row' => $row, 'Number' => $col, 'SeatType' => 'Normal', 'Status' => 'Available']);
                    $created++;
                }
            }

            $totalSeats = Seat::where('RoomId', $roomId)->count();
            Room::where('RoomId', $roomId)->update(['SeatCount' => $totalSeats]);
            DB::commit();

            return response()->json(['success' => true, 'message' => "Đã thêm {$created} ghế", 'data' => []]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

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

            $existingCols = Seat::where('RoomId', $roomId)->select('Number')->distinct()->pluck('Number')->toArray();
            $existingRows = Seat::where('RoomId', $roomId)->select('Row')->distinct()->pluck('Row')->toArray();

            if (empty($existingRows)) {
                $existingRows = ['A'];
                Seat::create(['RoomId' => $roomId, 'Row' => 'A', 'Number' => 1, 'SeatType' => 'Normal', 'Status' => 'Available']);
                $existingCols = [1];
            }

            $newCols = [];
            if ($position === 'start') {
                $minCol = !empty($existingCols) ? min($existingCols) : 1;
                for ($i = $minCol - 1; $i >= 1 && count($newCols) < $colCount; $i--) {
                    if (!in_array($i, $existingCols)) $newCols[] = $i;
                }
                sort($newCols);
            } else {
                $maxCol = !empty($existingCols) ? max($existingCols) : 0;
                for ($i = $maxCol + 1; $i <= $maxCol + $colCount; $i++) $newCols[] = $i;
            }

            if (empty($newCols)) {
                DB::rollBack();
                return response()->json(['success' => false, 'message' => 'Không có cột mới để thêm'], 400);
            }

            $created = 0;
            foreach ($existingRows as $row) {
                foreach ($newCols as $col) {
                    Seat::create(['RoomId' => $roomId, 'Row' => $row, 'Number' => $col, 'SeatType' => 'Normal', 'Status' => 'Available']);
                    $created++;
                }
            }

            $totalSeats = Seat::where('RoomId', $roomId)->count();
            Room::where('RoomId', $roomId)->update(['SeatCount' => $totalSeats]);
            DB::commit();

            return response()->json(['success' => true, 'message' => "Đã thêm {$created} ghế", 'data' => []]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateSeat(Request $request, $seatId)
    {
        $request->validate(['seat_type' => 'nullable|string', 'status' => 'nullable|string']);
        try {
            $seat = Seat::find($seatId);
            if (!$seat) return response()->json(['success' => false, 'message' => 'Không tìm thấy ghế'], 404);
            if ($request->has('seat_type')) $seat->SeatType = $request->seat_type;
            if ($request->has('status')) $seat->Status = $request->status;
            $seat->UpdatedAt = now(); $seat->save();
            return response()->json(['success' => true, 'message' => 'Cập nhật thành công']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteRow($roomId, $row)
    {
        try {
            $deleted = Seat::where('RoomId', $roomId)->where('Row', $row)->delete();
            if ($deleted === 0) return response()->json(['success' => false, 'message' => 'Không tìm thấy hàng'], 404);
            $totalSeats = Seat::where('RoomId', $roomId)->count();
            Room::where('RoomId', $roomId)->update(['SeatCount' => $totalSeats]);
            return response()->json(['success' => true, 'message' => "Đã xóa hàng {$row}"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteCol($roomId, $col)
    {
        try {
            $deleted = Seat::where('RoomId', $roomId)->where('Number', $col)->delete();
            if ($deleted === 0) return response()->json(['success' => false, 'message' => 'Không tìm thấy cột'], 404);
            $totalSeats = Seat::where('RoomId', $roomId)->count();
            Room::where('RoomId', $roomId)->update(['SeatCount' => $totalSeats]);
            return response()->json(['success' => true, 'message' => "Đã xóa cột {$col}"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteSeat($seatId)
    {
        try {
            $seat = Seat::find($seatId);
            if (!$seat) return response()->json(['success' => false, 'message' => 'Không tìm thấy ghế'], 404);
            $roomId = $seat->RoomId; $seat->delete();
            $totalSeats = Seat::where('RoomId', $roomId)->count();
            Room::where('RoomId', $roomId)->update(['SeatCount' => $totalSeats]);
            return response()->json(['success' => true, 'message' => 'Đã xóa ghế']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function resetSeats($roomId)
    {
        try {
            $updated = Seat::where('RoomId', $roomId)->where('Status', '!=', 'Available')->update(['Status' => 'Available']);
            return response()->json(['success' => true, 'message' => "Đã reset {$updated} ghế"]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}