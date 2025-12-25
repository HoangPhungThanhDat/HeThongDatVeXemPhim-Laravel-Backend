<?php

namespace App\Http\Controllers\Api;

use App\Models\Room;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Resources\RoomResource;
use App\Http\Controllers\Controller;
use App\Services\RoomService;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomService $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index()
    {
        $rooms = $this->roomService->getAll();
        return RoomResource::collection($rooms);
    }

    public function store(StoreRoomRequest $request)
    {
        $room = $this->roomService->create($request->validated());
        return new RoomResource($room);
    }

    public function show($RoomId)
    {
        $room = $this->roomService->getById($RoomId);
        return new RoomResource($room);
    }

    public function update(StoreRoomRequest $request, $RoomId)
    {
        $room = $this->roomService->update($RoomId, $request->validated());
        return new RoomResource($room);
    }

    public function destroy($RoomId)
    {
        $this->roomService->delete($RoomId);
        return response()->json(['message' => 'room deleted successfully']);
    }
}