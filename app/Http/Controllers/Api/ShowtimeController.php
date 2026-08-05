<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShowtimeRequest;
use App\Http\Resources\ShowtimeResource;
use App\Services\ShowtimeService;
use App\Models\Showtime;
use Illuminate\Http\Request;

/**
 * @group Showtimes
 * 
 * APIs for managing showtimes.
 * A showtime represents a specific screening of a movie at a specific time in a specific room.
 * Showtimes are linked to schedules and contain seat availability information.
 */
class ShowtimeController extends Controller
{
    protected $service;

    public function __construct(ShowtimeService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api', 'checkrole:Admin'])->only(['store', 'update', 'destroy']);
    }

    /**
     * Get all showtimes
     * 
     * Get a list of all showtimes in the system.
     * 
     * @unauthenticated
     * 
     * @response 200 [
     *   {
     *     "ShowtimeId": 1,
     *     "ScheduleId": 1,
     *     "MovieId": 1,
     *     "RoomId": 1,
     *     "StartTime": "2024-01-01 10:00:00",
     *     "EndTime": "2024-01-01 13:00:00",
     *     "Price": 120000,
     *     "Status": "Scheduled",
     *     "MovieTitle": "Avengers: Endgame",
     *     "RoomName": "Room 1",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   },
     *   {
     *     "ShowtimeId": 2,
     *     "ScheduleId": 1,
     *     "MovieId": 1,
     *     "RoomId": 1,
     *     "StartTime": "2024-01-01 13:00:00",
     *     "EndTime": "2024-01-01 16:00:00",
     *     "Price": 120000,
     *     "Status": "Scheduled",
     *     "MovieTitle": "Avengers: Endgame",
     *     "RoomName": "Room 1",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   }
     * ]
     */
    public function index()
    {
        return ShowtimeResource::collection($this->service->getAll());
    }

    /**
     * Get paginated showtimes
     * 
     * Get a paginated list of showtimes with search and filter options.
     * 
     * @unauthenticated
     * 
     * @queryParam page int The page number. Default: 1. Example: 1
     * @queryParam limit int Number of items per page. Default: 10. Max: 100. Example: 10
     * @queryParam search string Search by movie title. Example: Avengers
     * @queryParam status string Filter by status. Enum: Scheduled, Cancelled, Completed. Example: Scheduled
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "ShowtimeId": 1,
     *       "MovieTitle": "Avengers: Endgame",
     *       "StartTime": "2024-01-01 10:00:00",
     *       "Status": "Scheduled"
     *     }
     *   ],
     *   "total": 100,
     *   "page": 1,
     *   "limit": 10,
     *   "totalPages": 10
     * }
     */
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

    /**
     * Create a new showtime
     * 
     * Create a new showtime with movie, room, and time information. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam ScheduleId int required The ID of the schedule. Example: 1
     * @bodyParam MovieId int required The ID of the movie. Example: 1
     * @bodyParam RoomId int required The ID of the room. Example: 1
     * @bodyParam StartTime string required The start time of the show. Example: 2024-01-01 10:00:00
     * @bodyParam EndTime string required The end time of the show. Example: 2024-01-01 13:00:00
     * @bodyParam Price numeric required The ticket price. Example: 120000
     * @bodyParam Status string The showtime status. Enum: Scheduled, Cancelled, Completed. Example: Scheduled
     * 
     * @response 201 {
     *   "ShowtimeId": 1,
     *   "ScheduleId": 1,
     *   "MovieId": 1,
     *   "RoomId": 1,
     *   "StartTime": "2024-01-01 10:00:00",
     *   "EndTime": "2024-01-01 13:00:00",
     *   "Price": 120000,
     *   "Status": "Scheduled",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "MovieId": ["The movie id field is required."],
     *     "RoomId": ["The room id field is required."],
     *     "StartTime": ["The start time field is required."],
     *     "EndTime": ["The end time field is required."],
     *     "Price": ["The price field is required."]
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Forbidden - Admin only"
     * }
     */
    public function store(StoreShowtimeRequest $request)
    {
        $showtime = $this->service->create($request->validated());
        return new ShowtimeResource($showtime);
    }

    /**
     * Get a specific showtime
     * 
     * Get detailed information about a specific showtime.
     * 
     * @unauthenticated
     * 
     * @urlParam ShowtimeId int required The ID of the showtime. Example: 1
     * 
     * @response 200 {
     *   "ShowtimeId": 1,
     *   "ScheduleId": 1,
     *   "MovieId": 1,
     *   "RoomId": 1,
     *   "StartTime": "2024-01-01 10:00:00",
     *   "EndTime": "2024-01-01 13:00:00",
     *   "Price": 120000,
     *   "Status": "Scheduled",
     *   "MovieTitle": "Avengers: Endgame",
     *   "RoomName": "Room 1",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Showtime not found"
     * }
     */
    public function show($ShowtimeId)
    {
        $showtime = $this->service->find($ShowtimeId);
        if (!$showtime) {
            return response()->json(['message' => 'Showtime not found'], 404);
        }
        return new ShowtimeResource($showtime);
    }

    /**
     * Update a showtime
     * 
     * Update an existing showtime. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam ShowtimeId int required The ID of the showtime. Example: 1
     * @bodyParam ScheduleId int The ID of the schedule. Example: 1
     * @bodyParam MovieId int The ID of the movie. Example: 1
     * @bodyParam RoomId int The ID of the room. Example: 1
     * @bodyParam StartTime string The start time. Example: 2024-01-01 10:00:00
     * @bodyParam EndTime string The end time. Example: 2024-01-01 13:00:00
     * @bodyParam Price numeric The ticket price. Example: 130000
     * @bodyParam Status string The showtime status. Enum: Scheduled, Cancelled, Completed. Example: Cancelled
     * 
     * @response 200 {
     *   "ShowtimeId": 1,
     *   "StartTime": "2024-01-01 10:00:00",
     *   "EndTime": "2024-01-01 13:00:00",
     *   "Price": 130000,
     *   "Status": "Cancelled"
     * }
     * @response 404 {
     *   "message": "Showtime not found"
     * }
     */
    public function update(StoreShowtimeRequest $request, $ShowtimeId)
    {
        $showtime = $this->service->update($ShowtimeId, $request->validated());
        if (!$showtime) {
            return response()->json(['message' => 'Showtime not found'], 404);
        }
        return new ShowtimeResource($showtime);
    }

    /**
     * Delete a showtime
     * 
     * Delete a showtime from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam ShowtimeId int required The ID of the showtime. Example: 1
     * 
     * @response 200 {
     *   "message": "Showtime deleted successfully"
     * }
     * @response 404 {
     *   "message": "Showtime not found"
     * }
     */
    public function destroy($ShowtimeId)
    {
        $deleted = $this->service->delete($ShowtimeId);
        if (!$deleted) {
            return response()->json(['message' => 'Showtime not found'], 404);
        }
        return response()->json(['message' => 'Showtime deleted successfully']);
    }
}