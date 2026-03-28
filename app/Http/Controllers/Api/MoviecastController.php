<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMoviecastRequest;
use App\Http\Resources\MoviecastResource;
use App\Services\MoviecastService;
use App\Models\Moviecast;
use Illuminate\Http\Request;

class MoviecastController extends Controller
{
    protected $service;

    public function __construct(MoviecastService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api', 'checkrole:Admin'])->only(['store', 'update', 'destroy']);
    }

    // Lấy tất cả
    public function index()
    {
        return MoviecastResource::collection($this->service->getAll());
    }

    // ✅ Phân trang server-side
    // GET /api/moviecasts/paged?page=1&limit=10&search=tom&role=Actor
    public function getPaged(Request $request)
    {
        $page   = max(1, (int) $request->query('page',  1));
        $limit  = min(100, max(1, (int) $request->query('limit', 10)));
        $search = $request->query('search', '');
        $role   = $request->query('role', '');

        $query = Moviecast::with('movie'); // eager load movie nếu có relation

        if ($search) {
            $query->where('Name', 'like', "%{$search}%");
        }

        if ($role) {
            $query->where('Role', $role);
        }

        $total      = $query->count();
        $totalPages = (int) ceil($total / $limit);
        $items      = $query->orderBy('CastId', 'desc')
                            ->skip(($page - 1) * $limit)
                            ->take($limit)
                            ->get();

        return response()->json([
            'data'       => MoviecastResource::collection($items),
            'total'      => $total,
            'page'       => $page,
            'limit'      => $limit,
            'totalPages' => $totalPages,
        ]);
    }

    // Thêm
    public function store(StoreMoviecastRequest $request)
    {
        $moviecast = $this->service->create($request->validated());
        return new MoviecastResource($moviecast);
    }

    // Hiện 1
    public function show($CastId)
    {
        $moviecast = $this->service->find($CastId);
        if (!$moviecast) {
            return response()->json(['message' => 'Movie cast not found'], 404);
        }
        return new MoviecastResource($moviecast);
    }

    // Cập nhật
    public function update(StoreMoviecastRequest $request, $CastId)
    {
        $moviecast = $this->service->update($CastId, $request->validated());
        if (!$moviecast) {
            return response()->json(['message' => 'Movie cast not found'], 404);
        }
        return new MoviecastResource($moviecast);
    }

    // Xoá
    public function destroy($CastId)
    {
        $deleted = $this->service->delete($CastId);
        if (!$deleted) {
            return response()->json(['message' => 'Movie cast not found'], 404);
        }
        return response()->json(['message' => 'Movie cast deleted successfully']);
    }
}