<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RoomManagementService;
use App\Http\Requests\StoreRoomRequest;
use Illuminate\Http\Request;

class RoomManagementController extends Controller
{
    protected $roomManagementService;

    public function __construct(RoomManagementService $roomManagementService)
    {
        $this->roomManagementService = $roomManagementService;
    }

    public function getDashboardData(Request $request)
    {
        $result = $this->roomManagementService->getDashboardData();
        if (isset($result['error'])) return response()->json($result, 500);
        return response()->json($result);
    }

    public function getRoomDetail($roomId)
    {
        $result = $this->roomManagementService->getRoomDetail($roomId);
        if (isset($result['error'])) return response()->json($result, 500);
        return response()->json($result);
    }

    public function getStatistics(Request $request)
    {
        $result = $this->roomManagementService->getStatistics();
        if (isset($result['error'])) return response()->json($result, 500);
        return response()->json($result);
    }

    // ✅ THÊM HÀM NÀY: Cập nhật phòng
    public function updateRoom(StoreRoomRequest $request, $roomId)
    {
        try {
            $room = $this->roomManagementService->updateRoom($roomId, $request->validated());
            
            if (!$room) {
                return response()->json(['message' => 'Không tìm thấy phòng'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật phòng chiếu thành công',
                'data' => $room
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ✅ THÊM HÀM NÀY: Thêm phòng
    public function createRoom(StoreRoomRequest $request)
    {
        try {
            $room = $this->roomManagementService->createRoom($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Thêm phòng thành công',
                'data' => $room
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ✅ THÊM HÀM NÀY: Xóa phòng
    public function deleteRoom($roomId)
    {
        try {
            $result = $this->roomManagementService->deleteRoom($roomId);
            if (!$result) return response()->json(['message' => 'Không tìm thấy phòng'], 404);
            return response()->json(['success' => true, 'message' => 'Xóa phòng thành công']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updateSeats(Request $request, $roomId)
    {
        $request->validate([
            'seats' => 'required|array',
            'seats.*.SeatId' => 'required|exists:seats,SeatId',
            'seats.*.Row' => 'required|string|max:1',
            'seats.*.Number' => 'required|integer|min:1',
            'seats.*.SeatType' => 'required|in:Normal,VIP,Couple',
            'seats.*.Status' => 'required|in:Available,Booked,Reserved,Broken,Inactive',
        ]);

        $result = $this->roomManagementService->updateSeats($roomId, $request->seats);
        if (isset($result['error'])) return response()->json($result, 500);
        return response()->json($result);
    }
}