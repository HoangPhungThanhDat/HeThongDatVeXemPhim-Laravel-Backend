<?php
namespace App\Http\Controllers\Api;

use App\Models\Showtimeseat;
use App\Models\Showtime; // ✅ THÊM
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;  // ✅ THÊM
use App\Http\Requests\StoreShowtimeseatRequest;
use App\Http\Resources\ShowtimeseatResource;
use App\Http\Controllers\Controller;
use App\Services\ShowtimeseatService;

class ShowtimeseatController extends Controller
{
    protected $showtimeseatService;

    public function __construct(ShowtimeseatService $showtimeseatService)
    {
        $this->showtimeseatService = $showtimeseatService;
    }

    public function index()
    {
        $showtimeseats = $this->showtimeseatService->getAll();
        return ShowtimeseatResource::collection($showtimeseats);
    }

    public function store(StoreShowtimeseatRequest $request)
    {
        $showtimeseat = $this->showtimeseatService->create($request->validated());
        return new ShowtimeseatResource($showtimeseat);
    }

    public function show($ShowtimeSeatId)
    {
        $showtimeseat = $this->showtimeseatService->getById($ShowtimeSeatId);
        return new ShowtimeseatResource($showtimeseat);
    }

    public function update(StoreShowtimeseatRequest $request, $ShowtimeSeatId)
    {
        $showtimeseat = $this->showtimeseatService->update($ShowtimeSeatId, $request->validated());
        return new ShowtimeseatResource($showtimeseat);
    }

    public function destroy($ShowtimeSeatId)
    {
        $this->showtimeseatService->delete($ShowtimeSeatId);
        return response()->json(['message' => 'showtimeseat deleted successfully']);
    }

    // GET /api/showtimes/{showtimeId}/seats
    public function getSeatsByShowtime($showtimeId)
    {
        $seats = $this->showtimeseatService->getSeatsByShowtimeId($showtimeId);

        return response()->json([
            'success' => true,
            'data'    => $seats,
        ]);
    }

    // ✅ THÊM MỚI: POST /api/showtimes/{showtimeId}/generate-seats
    public function generateSeats($showtimeId): JsonResponse
    {
        $showtime = Showtime::findOrFail($showtimeId);

        $this->showtimeseatService->generateSeatsForShowtime(
            $showtime->ShowtimeId,
            $showtime->RoomId
        );

        return response()->json([
            'success' => true,
            'message' => 'Tạo ghế tự động thành công!',
        ]);
    }
}