<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Resources\TicketResource;
use App\Services\TicketService;

/**
 * @group Tickets
 * 
 * APIs for managing movie tickets.
 * Tickets are generated when a user books a seat for a showtime.
 * Each ticket is associated with a specific seat, showtime, and user.
 */
class TicketController extends Controller
{
    protected $service;

    public function __construct(TicketService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }

    /**
     * Get all tickets
     * 
     * Get a list of all tickets in the system. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "TicketId": 1,
     *     "OrderDetailId": 1,
     *     "ShowtimeId": 1,
     *     "SeatId": 1,
     *     "UserId": 1,
     *     "FullName": "Nguyễn Văn A",
     *     "Email": "user@example.com",
     *     "TicketCode": "TKT-2024-001",
     *     "Price": 120000,
     *     "Status": "Active",
     *     "BookingTime": "2024-01-01 10:00:00",
     *     "MovieTitle": "Avengers: Endgame",
     *     "ShowtimeStart": "2024-01-01 10:00:00",
     *     "RoomName": "Room 1",
     *     "SeatLabel": "A1",
     *     "SeatType": "Normal",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   }
     * ]
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Forbidden - Admin only"
     * }
     */
    public function index()
    {
        return TicketResource::collection($this->service->getAll());
    }

    /**
     * Create a new ticket
     * 
     * Create a new ticket for a booking. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam OrderDetailId int required The ID of the order detail. Example: 1
     * @bodyParam ShowtimeId int required The ID of the showtime. Example: 1
     * @bodyParam SeatId int required The ID of the seat. Example: 1
     * @bodyParam UserId int required The ID of the user. Example: 1
     * @bodyParam TicketCode string required The unique ticket code. Example: TKT-2024-001
     * @bodyParam Price numeric required The ticket price. Example: 120000
     * @bodyParam Status string The ticket status. Enum: Active, Used, Cancelled. Example: Active
     * @bodyParam BookingTime string The booking time. Example: 2024-01-01 10:00:00
     * 
     * @response 201 {
     *   "TicketId": 1,
     *   "OrderDetailId": 1,
     *   "ShowtimeId": 1,
     *   "SeatId": 1,
     *   "UserId": 1,
     *   "TicketCode": "TKT-2024-001",
     *   "Price": 120000,
     *   "Status": "Active",
     *   "BookingTime": "2024-01-01 10:00:00",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "OrderDetailId": ["The order detail id field is required."]
     *   }
     * }
     */
    public function store(StoreTicketRequest $request)
    {
        $ticket = $this->service->create($request->validated());
        return new TicketResource($ticket);
    }

    /**
     * Get a specific ticket
     * 
     * Get detailed information about a specific ticket. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam TicketId int required The ID of the ticket. Example: 1
     * 
     * @response 200 {
     *   "TicketId": 1,
     *   "OrderDetailId": 1,
     *   "ShowtimeId": 1,
     *   "SeatId": 1,
     *   "UserId": 1,
     *   "FullName": "Nguyễn Văn A",
     *   "Email": "user@example.com",
     *   "TicketCode": "TKT-2024-001",
     *   "Price": 120000,
     *   "Status": "Active",
     *   "BookingTime": "2024-01-01 10:00:00",
     *   "MovieTitle": "Avengers: Endgame",
     *   "ShowtimeStart": "2024-01-01 10:00:00",
     *   "RoomName": "Room 1",
     *   "SeatLabel": "A1",
     *   "SeatType": "Normal",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Ticket not found"
     * }
     */
    public function show($TicketId)
    {
        $ticket = $this->service->find($TicketId);
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        return new TicketResource($ticket);
    }

    /**
     * Update a ticket
     * 
     * Update an existing ticket. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam TicketId int required The ID of the ticket. Example: 1
     * @bodyParam OrderDetailId int The ID of the order detail. Example: 1
     * @bodyParam ShowtimeId int The ID of the showtime. Example: 1
     * @bodyParam SeatId int The ID of the seat. Example: 1
     * @bodyParam UserId int The ID of the user. Example: 1
     * @bodyParam TicketCode string The ticket code. Example: TKT-2024-001-UPDATED
     * @bodyParam Price numeric The ticket price. Example: 130000
     * @bodyParam Status string The ticket status. Enum: Active, Used, Cancelled. Example: Used
     * @bodyParam BookingTime string The booking time. Example: 2024-01-01 10:00:00
     * 
     * @response 200 {
     *   "TicketId": 1,
     *   "TicketCode": "TKT-2024-001-UPDATED",
     *   "Price": 130000,
     *   "Status": "Used"
     * }
     * @response 404 {
     *   "message": "Ticket not found"
     * }
     */
    public function update(StoreTicketRequest $request, $TicketId)
    {
        $ticket = $this->service->update($TicketId, $request->validated());
        if (!$ticket) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        return new TicketResource($ticket);
    }

    /**
     * Delete a ticket
     * 
     * Delete a ticket from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam TicketId int required The ID of the ticket. Example: 1
     * 
     * @response 200 {
     *   "message": "Ticket deleted successfully"
     * }
     * @response 404 {
     *   "message": "Ticket not found"
     * }
     */
    public function destroy($TicketId)
    {
        $deleted = $this->service->delete($TicketId);
        if (!$deleted) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }
        return response()->json(['message' => 'Ticket deleted successfully']);
    }
}