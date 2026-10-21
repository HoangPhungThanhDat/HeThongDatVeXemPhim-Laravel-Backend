<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMoviecastRequest;
use App\Http\Resources\MoviecastResource;
use App\Services\MoviecastService;
use Illuminate\Http\Request;

/**
 * @group Movie Casts
 * 
 * APIs for managing movie casts (actors, directors, etc.).
 * Each movie cast member can be associated with multiple movies.
 */
class MoviecastController extends Controller
{
    protected $service;

    public function __construct(MoviecastService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api', 'checkrole:Admin'])->only(['store', 'update', 'destroy']);
    }

    /**
     * Get all movie casts
     */
    public function index()
    {
        return MoviecastResource::collection($this->service->getAll());
    }

    /**
     * Get paginated movie casts
     * 
     * @queryParam page int The page number. Default: 1.
     * @queryParam limit int Number of items per page. Default: 10. Max: 100.
     * @queryParam search string Search by name.
     * @queryParam role string Filter by role. Enum: Actor, Director, Producer, Writer.
     * @queryParam status string Filter by status. Enum: Active, Inactive.
     */
    public function getPaged(Request $request)
    {
        $page   = max(1, (int) $request->query('page',  1));
        $limit  = min(100, max(1, (int) $request->query('limit', 10)));
        $search = $request->query('search', '');
        $role   = $request->query('role', '');
        $status = $request->query('status', ''); // ✅ Lấy status từ URL

        // ✅ Truyền thêm $status xuống Service
        $result = $this->service->getPaged($page, $limit, $search, $role, $status);

        return response()->json([
            'data'       => MoviecastResource::collection($result['data']),
            'total'      => $result['total'],
            'page'       => $result['page'],
            'limit'      => $result['limit'],
            'totalPages' => $result['totalPages'],
        ]);
    }

    /**
     * Create a new movie cast
     */
    public function store(StoreMoviecastRequest $request)
    {
        $moviecast = $this->service->create($request->validated());
        return new MoviecastResource($moviecast);
    }

    /**
     * Get a specific movie cast
     */
    public function show($CastId)
    {
        $moviecast = $this->service->find($CastId);
        if (!$moviecast) {
            return response()->json(['message' => 'Movie cast not found'], 404);
        }
        return new MoviecastResource($moviecast);
    }

    /**
     * Update a movie cast
     */
    public function update(StoreMoviecastRequest $request, $CastId)
    {
        $moviecast = $this->service->update($CastId, $request->validated());
        if (!$moviecast) {
            return response()->json(['message' => 'Movie cast not found'], 404);
        }
        return new MoviecastResource($moviecast);
    }

    /**
     * Delete a movie cast
     */
    public function destroy($CastId)
    {
        $deleted = $this->service->delete($CastId);
        if (!$deleted) {
            return response()->json(['message' => 'Movie cast not found'], 404);
        }
        return response()->json(['message' => 'Movie cast deleted successfully']);
    }
}