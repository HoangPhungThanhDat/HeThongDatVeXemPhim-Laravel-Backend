<?php

namespace App\Http\Controllers\Api;

use App\Models\Seat;
use Illuminate\Http\Request;
use App\Http\Requests\StoreSeatRequest;
use App\Http\Resources\SeatResource;
use App\Http\Controllers\Controller;
use App\Services\SeatService;

class SeatController extends Controller
{
    protected $seatService;

    public function __construct(SeatService $seatService)
    {
        $this->seatService = $seatService;
    }

    public function index()
    {
        $seats = $this->seatService->getAll();
        return SeatResource::collection($seats);
    }

    public function store(StoreSeatRequest $request)
    {
        $seat = $this->seatService->create($request->validated());
        return new SeatResource($seat);
    }

    public function show($SeatId)
    {
        $seat = $this->seatService->getById($SeatId);
        return new SeatResource($seat);
    }

    public function update(StoreSeatRequest $request, $SeatId)
    {
        $seat = $this->seatService->update($SeatId, $request->validated());
        return new SeatResource($seat);
    }

    public function destroy($SeatId)
    {
        $this->seatService->delete($SeatId);
        return response()->json(['message' => 'seat deleted successfully']);
    }
}