<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SeatManagementService;
use Illuminate\Http\Request;

class SeatManagementController extends Controller
{
    protected $seatManagementService;

    public function __construct(SeatManagementService $seatManagementService)
    {
        $this->seatManagementService = $seatManagementService;
    }

    public function getData(Request $request)
    {
        $result = $this->seatManagementService->getData($request);
        
        if (isset($result['error'])) {
            return response()->json($result, 500);
        }
        return response()->json($result);
    }

    public function getSeatsByShowtime($showtimeId, Request $request)
    {
        $result = $this->seatManagementService->getSeatsByShowtime($showtimeId, $request);
        
        if (isset($result['error'])) {
            return response()->json($result, 500);
        }
        return response()->json($result);
    }

    public function generateSeats(Request $request)
    {
        $request->validate([
            'showtime_id' => 'required|exists:showtimes,ShowtimeId',
        ]);

        $result = $this->seatManagementService->generateSeats($request->showtime_id);
        
        if (isset($result['error'])) {
            return response()->json($result, isset($result['code']) ? $result['code'] : 500);
        }
        return response()->json($result);
    }

    public function updateSeat(Request $request)
    {
        $request->validate([
            'showtime_seat_id' => 'required|exists:showtimeseats,ShowtimeSeatId',
            'status' => 'required|in:Available,Reserved,Held,Broken,Inactive',
        ]);

        $result = $this->seatManagementService->updateSeat(
            $request->showtime_seat_id, 
            $request->status
        );
        
        if (isset($result['error'])) {
            return response()->json($result, isset($result['code']) ? $result['code'] : 500);
        }
        return response()->json($result);
    }

    public function resetSeat(Request $request)
    {
        $request->validate([
            'showtime_seat_id' => 'required|exists:showtimeseats,ShowtimeSeatId',
        ]);

        $result = $this->seatManagementService->resetSeat($request->showtime_seat_id);
        
        if (isset($result['error'])) {
            return response()->json($result, isset($result['code']) ? $result['code'] : 500);
        }
        return response()->json($result);
    }

    public function deleteSeat(Request $request)
    {
        $request->validate([
            'showtime_seat_id' => 'required|exists:showtimeseats,ShowtimeSeatId',
        ]);

        $result = $this->seatManagementService->deleteSeat($request->showtime_seat_id);
        
        if (isset($result['error'])) {
            return response()->json($result, isset($result['code']) ? $result['code'] : 500);
        }
        return response()->json($result);
    }

    public function deleteAllSeatsByShowtime($showtimeId)
    {
        $result = $this->seatManagementService->deleteAllSeatsByShowtime($showtimeId);
        
        if (isset($result['error'])) {
            return response()->json($result, 500);
        }
        return response()->json($result);
    }
}