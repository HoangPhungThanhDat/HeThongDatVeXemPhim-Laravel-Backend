<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TicketManagementService;
use Illuminate\Http\Request;

class TicketManagementController extends Controller
{
    protected $ticketManagementService;

    public function __construct(TicketManagementService $ticketManagementService)
    {
        $this->ticketManagementService = $ticketManagementService;
    }

    public function getDashboardData(Request $request)
    {
        $result = $this->ticketManagementService->getDashboardData();
        
        if (isset($result['error'])) {
            return response()->json($result, 500);
        }
        return response()->json($result);
    }

    public function getTicketDetail($ticketId)
    {
        $result = $this->ticketManagementService->getTicketDetail($ticketId);
        
        if (isset($result['error'])) {
            return response()->json($result, 500);
        }
        return response()->json($result);
    }

    public function getStatistics(Request $request)
    {
        $result = $this->ticketManagementService->getStatistics();
        
        if (isset($result['error'])) {
            return response()->json($result, 500);
        }
        return response()->json($result);
    }

    public function storeTickets(Request $request)
    {
        $request->validate([
            'userId' => 'required|exists:users,UserId',
            'showtimeId' => 'required|exists:showtimes,ShowtimeId',
            'seats' => 'required|array|min:1',
            'seats.*.seatId' => 'required|exists:seats,SeatId',
            'seats.*.showtimeSeatId' => 'required|exists:showtimeseats,Id',
            'status' => 'nullable|in:Paid,CheckedIn,Pending,Cancelled,Expired',
            'note' => 'nullable|string|max:500',
            'comboId' => 'nullable|exists:foodanddrinks,ItemId',
        ]);

        $result = $this->ticketManagementService->storeTickets($request->all());
        
        if (isset($result['error'])) {
            return response()->json($result, 500);
        }
        return response()->json($result);
    }

    public function updateTicket(Request $request, $ticketId)
    {
        $request->validate([
            'userId' => 'nullable|exists:users,UserId',
            'showtimeId' => 'nullable|exists:showtimes,ShowtimeId',
            'seatId' => 'nullable|exists:seats,SeatId',
            'status' => 'nullable|in:Paid,CheckedIn,Pending,Cancelled,Expired',
            'note' => 'nullable|string|max:500',
            'oldShowtimeSeatId' => 'nullable|exists:showtimeseats,Id',
            'newShowtimeSeatId' => 'nullable|exists:showtimeseats,Id',
            'comboId' => 'nullable|exists:foodanddrinks,ItemId',
        ]);

        $result = $this->ticketManagementService->updateTicket($ticketId, $request->all());
        
        if (isset($result['error'])) {
            return response()->json($result, 500);
        }
        return response()->json($result);
    }

    public function deleteTicket($ticketId)
    {
        $result = $this->ticketManagementService->deleteTicket($ticketId);
        
        if (isset($result['error'])) {
            return response()->json($result, 500);
        }
        return response()->json($result);
    }
}