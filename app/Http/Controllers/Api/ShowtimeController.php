<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShowtimeRequest;
use App\Http\Resources\ShowtimeResource;
use App\Services\ShowtimeService;
use App\Models\Showtime;
use Illuminate\Http\Request;

class ShowtimeController extends Controller
{
    protected $service;

    public function __construct(ShowtimeService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api', 'checkrole:Admin'])->only(['store', 'update', 'destroy']);
    }

    // Lấy tất cả
    public function index()
    {
        return ShowtimeResource::collection($this->service->getAll());
    }

    // ✅ Phân trang server-side
    // GET /api/showtimes/paged?page=1&limit=10&search=avengers&status=Scheduled
    public function getPaged(Request $request)
    {
        $page   = max(1, (int) $request->query('page',  1));
        $limit  = min(100, max(1, (int) $request->query('limit', 10)));
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        // Eager load Movie và Room để hiện tên phim + phòng
        $query = Showtime::with(['Movie', 'Room']);

        if ($search) {
            $query->whereHas('Movie', function ($q) use ($search) {
                $q->where('Title', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('Status', $status);
        }

        $total      = $query->count();
        $totalPages = (int) ceil($total / $limit);
        $items      = $query->orderBy('StartTime', 'desc')
                            ->skip(($page - 1) * $limit)
                            ->take($limit)
                            ->get();

        return response()->json([
            'data'       => ShowtimeResource::collection($items),
            'total'      => $total,
            'page'       => $page,
            'limit'      => $limit,
            'totalPages' => $totalPages,
        ]);
    }

    // Thêm
    public function store(StoreShowtimeRequest $request)
    {
        $showtime = $this->service->create($request->validated());
        return new ShowtimeResource($showtime);
    }

    // Hiện 1
    public function show($ShowtimeId)
    {
        $showtime = $this->service->find($ShowtimeId);
        if (!$showtime) {
            return response()->json(['message' => 'Showtime not found'], 404);
        }
        return new ShowtimeResource($showtime);
    }

    // Cập nhật
    public function update(StoreShowtimeRequest $request, $ShowtimeId)
    {
        $showtime = $this->service->update($ShowtimeId, $request->validated());
        if (!$showtime) {
            return response()->json(['message' => 'Showtime not found'], 404);
        }
        return new ShowtimeResource($showtime);
    }

    // Xoá
    public function destroy($ShowtimeId)
    {
        $deleted = $this->service->delete($ShowtimeId);
        if (!$deleted) {
            return response()->json(['message' => 'Showtime not found'], 404);
        }
        return response()->json(['message' => 'Showtime deleted successfully']);
    }
}