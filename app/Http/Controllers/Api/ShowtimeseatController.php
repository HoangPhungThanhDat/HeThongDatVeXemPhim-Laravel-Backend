<?php

namespace App\Http\Controllers\Api;

use App\Models\Showtimeseat;
use App\Models\Showtime;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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

    // ✅ Phân trang server-side
    public function getPaged(Request $request): JsonResponse
    {
        $page       = max(1, (int) $request->query('page',  1));
        $limit      = min(100, max(1, (int) $request->query('limit', 20)));
        $showtimeId = $request->query('showtime_id', '');
        $status     = $request->query('status', '');

        $query = Showtimeseat::with(['seat', 'showtime.movie', 'showtime.room']);

        if ($showtimeId) {
            $query->where('ShowtimeId', $showtimeId);
        }

        if ($status) {
            $query->where('Status', $status);
        }

        $total      = $query->count();
        $totalPages = (int) ceil($total / $limit);
        $items      = $query->orderBy('ShowtimeSeatId', 'asc')
                            ->skip(($page - 1) * $limit)
                            ->take($limit)
                            ->get();

        return response()->json([
            'data'       => ShowtimeseatResource::collection($items),
            'total'      => $total,
            'page'       => $page,
            'limit'      => $limit,
            'totalPages' => $totalPages,
        ]);
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

    // POST /api/showtimes/{showtimeId}/generate-seats
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