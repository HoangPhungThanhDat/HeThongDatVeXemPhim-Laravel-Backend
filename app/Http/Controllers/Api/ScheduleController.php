<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Services\ScheduleService;
use Illuminate\Http\Request;

/**
 * @group Schedules
 * 
 * APIs for managing movie schedules.
 * A schedule defines when movies are shown in specific rooms.
 * Creating a schedule automatically generates showtimes and seat assignments.
 */
class ScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * Get all schedules
     * 
     * Get a list of all movie schedules.
     * 
     * @unauthenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "ScheduleId": 1,
     *       "MovieId": 1,
     *       "CinemaId": 1,
     *       "StartDate": "2024-01-01",
     *       "EndDate": "2024-01-31",
     *       "Showtimes": [
     *         {
     *           "ShowtimeId": 1,
     *           "StartTime": "10:00:00",
     *           "EndTime": "13:00:00"
     *         }
     *       ],
     *       "MovieTitle": "Avengers: Endgame",
     *       "CinemaName": "CGV Vincom Center",
     *       "RoomName": "Room 1",
     *       "CreatedAt": "2024-01-01 10:00:00"
     *     }
     *   ]
     * }
     */
    public function index()
    {
        $schedules = $this->scheduleService->getAll();
        return response()->json([
            'success' => true,
            'data'    => ScheduleResource::collection($schedules),
        ]);
    }

    /**
     * Create a new schedule
     * 
     * Create a new movie schedule. This will automatically generate showtimes
     * and seat assignments for each showtime. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam MovieId int required The ID of the movie. Example: 1
     * @bodyParam CinemaId int required The ID of the cinema. Example: 1
     * @bodyParam RoomId int required The ID of the room. Example: 1
     * @bodyParam StartDate string required The start date of the schedule. Example: 2024-01-01
     * @bodyParam EndDate string required The end date of the schedule. Example: 2024-01-31
     * @bodyParam Showtimes array required The list of showtimes. Example: ["10:00", "13:00", "16:00"]
     * @bodyParam Price numeric The ticket price. Example: 120000
     * 
     * @response 201 {
     *   "success": true,
     *   "message": "Tạo lịch chiếu thành công",
     *   "data": {
     *     "ScheduleId": 1,
     *     "MovieId": 1,
     *     "StartDate": "2024-01-01",
     *     "EndDate": "2024-01-31"
     *   },
     *   "meta": {
     *     "showtimes_created": 6,
     *     "seats_created": 600
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "MovieId": ["The movie id field is required."],
     *     "CinemaId": ["The cinema id field is required."],
     *     "RoomId": ["The room id field is required."],
     *     "StartDate": ["The start date field is required."],
     *     "EndDate": ["The end date field is required."],
     *     "Showtimes": ["The showtimes field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Tạo lịch chiếu thất bại: ..."
     * }
     */
    public function store(StoreScheduleRequest $request)
    {
        try {
            $result = $this->scheduleService->create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Tạo lịch chiếu thành công',
                'data'    => new ScheduleResource($result['schedule']),
                // Thông tin bổ sung để frontend hiển thị toast chi tiết
                'meta'    => [
                    'showtimes_created' => $result['showtimes_created'],
                    'seats_created'     => $result['seats_created'],
                ],
            ], 201);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Tạo lịch chiếu thất bại: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific schedule
     * 
     * Get detailed information about a specific schedule.
     * 
     * @unauthenticated
     * 
     * @urlParam id int required The ID of the schedule. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "ScheduleId": 1,
     *     "MovieId": 1,
     *     "CinemaId": 1,
     *     "RoomId": 1,
     *     "StartDate": "2024-01-01",
     *     "EndDate": "2024-01-31",
     *     "MovieTitle": "Avengers: Endgame",
     *     "CinemaName": "CGV Vincom Center",
     *     "RoomName": "Room 1",
     *     "Showtimes": [...]
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Schedule not found"
     * }
     */
    public function show($id)
    {
        $schedule = $this->scheduleService->getById($id);
        return response()->json([
            'success' => true,
            'data'    => new ScheduleResource($schedule),
        ]);
    }

    /**
     * Update a schedule
     * 
     * Update an existing schedule. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam id int required The ID of the schedule. Example: 1
     * @bodyParam MovieId int The ID of the movie. Example: 1
     * @bodyParam CinemaId int The ID of the cinema. Example: 1
     * @bodyParam RoomId int The ID of the room. Example: 1
     * @bodyParam StartDate string The start date. Example: 2024-01-01
     * @bodyParam EndDate string The end date. Example: 2024-02-15
     * @bodyParam Showtimes array The list of showtimes. Example: ["10:00", "14:00", "18:00"]
     * @bodyParam Price numeric The ticket price. Example: 130000
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Cập nhật lịch chiếu thành công",
     *   "data": {
     *     "ScheduleId": 1,
     *     "StartDate": "2024-01-01",
     *     "EndDate": "2024-02-15"
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Schedule not found"
     * }
     */
    public function update(StoreScheduleRequest $request, $id)
    {
        $schedule = $this->scheduleService->update($id, $request->validated());
        return response()->json([
            'success' => true,
            'message' => 'Cập nhật lịch chiếu thành công',
            'data'    => new ScheduleResource($schedule),
        ]);
    }

    /**
     * Delete a schedule
     * 
     * Delete a schedule and all its associated showtimes and seats. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam id int required The ID of the schedule. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Xóa lịch chiếu thành công"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Schedule not found"
     * }
     */
    public function destroy($id)
    {
        $this->scheduleService->delete($id);
        return response()->json([
            'success' => true,
            'message' => 'Xóa lịch chiếu thành công',
        ]);
    }
}