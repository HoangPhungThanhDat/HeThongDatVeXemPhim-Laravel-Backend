<?php

namespace App\Http\Controllers\Api;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Resources\RoomResource;
use App\Http\Controllers\Controller;
use App\Services\RoomService;

/**
 * @group Rooms
 * 
 * APIs for managing cinema rooms.
 * Each cinema has multiple rooms with different capacities and types (2D, 3D, IMAX, etc.).
 */
class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    /**
     * Get all rooms
     * 
     * Get a list of all cinema rooms in the system.
     * 
     * @unauthenticated
     * 
     * @response 200 [
     *   {
     *     "RoomId": 1,
     *     "CinemaId": 1,
     *     "Name": "Room 1 - VIP",
     *     "RoomType": "2D",
     *     "Capacity": 100,
     *     "Status": "Active",
     *     "Description": "Standard 2D room with comfortable seating",
     *     "CinemaName": "CGV Vincom Center",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   },
     *   {
     *     "RoomId": 2,
     *     "CinemaId": 1,
     *     "Name": "Room 2 - IMAX",
     *     "RoomType": "IMAX",
     *     "Capacity": 200,
     *     "Status": "Active",
     *     "Description": "IMAX room with large screen and premium sound",
     *     "CinemaName": "CGV Vincom Center",
     *     "CreatedAt": "2024-01-01 10:05:00"
     *   },
     *   {
     *     "RoomId": 3,
     *     "CinemaId": 2,
     *     "Name": "Room A - 3D",
     *     "RoomType": "3D",
     *     "Capacity": 80,
     *     "Status": "Inactive",
     *     "Description": "3D room under maintenance",
     *     "CinemaName": "Lotte Cinema",
     *     "CreatedAt": "2024-01-01 10:10:00"
     *   }
     * ]
     */
    public function index()
    {
        $rooms = $this->roomService->getAll();
        return RoomResource::collection($rooms);
    }

    /**
     * Create a new room
     * 
     * Create a new cinema room. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam CinemaId int required The ID of the cinema. Example: 1
     * @bodyParam Name string required The room name. Example: Room 1 - VIP
     * @bodyParam RoomType string required The room type. Enum: 2D, 3D, IMAX, 4DX, VIP. Example: 2D
     * @bodyParam Capacity int required The seating capacity. Example: 100
     * @bodyParam Status string The room status. Enum: Active, Inactive, Maintenance. Example: Active
     * @bodyParam Description string The room description. Example: Standard 2D room with comfortable seating
     * 
     * @response 201 {
     *   "RoomId": 1,
     *   "CinemaId": 1,
     *   "Name": "Room 1 - VIP",
     *   "RoomType": "2D",
     *   "Capacity": 100,
     *   "Status": "Active",
     *   "Description": "Standard 2D room with comfortable seating",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "CinemaId": ["The cinema id field is required."],
     *     "Name": ["The name field is required."],
     *     "RoomType": ["The room type field is required."],
     *     "Capacity": ["The capacity field is required."]
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Forbidden - Admin only"
     * }
     */
    public function store(StoreRoomRequest $request)
    {
        $room = $this->roomService->create($request->validated());
        return new RoomResource($room);
    }

    /**
     * Get a specific room
     * 
     * Get detailed information about a specific cinema room.
     * 
     * @unauthenticated
     * 
     * @urlParam RoomId int required The ID of the room. Example: 1
     * 
     * @response 200 {
     *   "RoomId": 1,
     *   "CinemaId": 1,
     *   "Name": "Room 1 - VIP",
     *   "RoomType": "2D",
     *   "Capacity": 100,
     *   "Status": "Active",
     *   "Description": "Standard 2D room with comfortable seating",
     *   "CinemaName": "CGV Vincom Center",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Room not found"
     * }
     */
    public function show($RoomId)
    {
        $room = $this->roomService->getById($RoomId);
        return new RoomResource($room);
    }

    /**
     * Update a room
     * 
     * Update an existing cinema room. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam RoomId int required The ID of the room. Example: 1
     * @bodyParam CinemaId int The ID of the cinema. Example: 1
     * @bodyParam Name string The room name. Example: Room 1 - VIP Updated
     * @bodyParam RoomType string The room type. Enum: 2D, 3D, IMAX, 4DX, VIP. Example: IMAX
     * @bodyParam Capacity int The seating capacity. Example: 150
     * @bodyParam Status string The room status. Enum: Active, Inactive, Maintenance. Example: Maintenance
     * @bodyParam Description string The room description. Example: Under renovation
     * 
     * @response 200 {
     *   "RoomId": 1,
     *   "CinemaId": 1,
     *   "Name": "Room 1 - VIP Updated",
     *   "RoomType": "IMAX",
     *   "Capacity": 150,
     *   "Status": "Maintenance",
     *   "Description": "Under renovation",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Room not found"
     * }
     */
    public function update(StoreRoomRequest $request, $RoomId)
    {
        $room = $this->roomService->update($RoomId, $request->validated());
        return new RoomResource($room);
    }

    /**
     * Delete a room
     * 
     * Delete a cinema room from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam RoomId int required The ID of the room. Example: 1
     * 
     * @response 200 {
     *   "message": "Room deleted successfully"
     * }
     * @response 404 {
     *   "message": "Room not found"
     * }
     */
    public function destroy($RoomId)
    {
        $this->roomService->delete($RoomId);
        return response()->json(['message' => 'Room deleted successfully']);
    }
}