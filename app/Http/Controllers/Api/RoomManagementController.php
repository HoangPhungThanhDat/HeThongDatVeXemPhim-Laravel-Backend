<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RoomManagementService;
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

        if (isset($result['error'])) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }

    public function getRoomDetail($roomId)
    {
        $result = $this->roomManagementService->getRoomDetail($roomId);

        if (isset($result['error'])) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }

    public function getStatistics(Request $request)
    {
        $result = $this->roomManagementService->getStatistics();

        if (isset($result['error'])) {
            return response()->json($result, 500);
        }

        return response()->json($result);
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

        if (isset($result['error'])) {
            return response()->json($result, 500);
        }

        return response()->json($result);
    }
}