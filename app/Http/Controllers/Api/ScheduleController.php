<?php

// namespace App\Http\Controllers\Api;

// use App\Models\Schedule;
// use Illuminate\Http\Request;
// use App\Http\Requests\StoreScheduleRequest;
// use App\Http\Resources\ScheduleResource;
// use App\Http\Controllers\Controller;
// use App\Services\ScheduleService;


// class ScheduleController extends Controller
// {
//     protected $scheduleService;

//     public function __construct(ScheduleService $scheduleService)
//     {
//         $this->scheduleService = $scheduleService;
//     }

//     public function index()
//     {
//         $schedules = $this->scheduleService->getAll();
//         return ScheduleResource::collection($schedules);
//     }

//     public function store(StoreScheduleRequest $request)
//     {
//         $data = $request->validated();
    
//         // Convert DaysOfWeek array → CSV string
//         if (isset($data['DaysOfWeek']) && is_array($data['DaysOfWeek'])) {
//             $data['DaysOfWeek'] = implode(',', $data['DaysOfWeek']);
//         }
    
//         $schedule = $this->scheduleService->create($data);
//         return new ScheduleResource($schedule);
//     }
    
//     public function update(StoreScheduleRequest $request, $ScheduleId)
//     {
//         $data = $request->validated();
    
//         // Convert DaysOfWeek array → CSV string
//         if (isset($data['DaysOfWeek']) && is_array($data['DaysOfWeek'])) {
//             $data['DaysOfWeek'] = implode(',', $data['DaysOfWeek']);
//         }
    
//         $schedule = $this->scheduleService->update($ScheduleId, $data);
//         return new ScheduleResource($schedule);
//     }
    

//     public function show($ScheduleId)
//     {
//         $schedule = $this->scheduleService->getById($ScheduleId);
//         return new ScheduleResource($schedule);
//     }

  

//     public function destroy($ScheduleId)
//     {
//         $this->scheduleService->delete($ScheduleId);
//         return response()->json(['message' => 'schedule deleted successfully']);
//     }
// }





























namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Services\ScheduleService;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    /**
     * GET /api/schedules
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
     * POST /api/schedules
     * Tạo Schedule → tự động sinh Showtimes + ShowtimeSeats
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
     * GET /api/schedules/{id}
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
     * PUT /api/schedules/{id}
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
     * DELETE /api/schedules/{id}
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