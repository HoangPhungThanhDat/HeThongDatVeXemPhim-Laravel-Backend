<?php

namespace App\Http\Controllers\Api;

use App\Models\Schedule;
use Illuminate\Http\Request;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Http\Controllers\Controller;
use App\Services\ScheduleService;


class ScheduleController extends Controller
{
    protected $scheduleService;

    public function __construct(ScheduleService $scheduleService)
    {
        $this->scheduleService = $scheduleService;
    }

    public function index()
    {
        $schedules = $this->scheduleService->getAll();
        return ScheduleResource::collection($schedules);
    }

    public function store(StoreScheduleRequest $request)
    {
        $data = $request->validated();
    
        // Convert DaysOfWeek array → CSV string
        if (isset($data['DaysOfWeek']) && is_array($data['DaysOfWeek'])) {
            $data['DaysOfWeek'] = implode(',', $data['DaysOfWeek']);
        }
    
        $schedule = $this->scheduleService->create($data);
        return new ScheduleResource($schedule);
    }
    
    public function update(StoreScheduleRequest $request, $ScheduleId)
    {
        $data = $request->validated();
    
        // Convert DaysOfWeek array → CSV string
        if (isset($data['DaysOfWeek']) && is_array($data['DaysOfWeek'])) {
            $data['DaysOfWeek'] = implode(',', $data['DaysOfWeek']);
        }
    
        $schedule = $this->scheduleService->update($ScheduleId, $data);
        return new ScheduleResource($schedule);
    }
    

    public function show($ScheduleId)
    {
        $schedule = $this->scheduleService->getById($ScheduleId);
        return new ScheduleResource($schedule);
    }

  

    public function destroy($ScheduleId)
    {
        $this->scheduleService->delete($ScheduleId);
        return response()->json(['message' => 'schedule deleted successfully']);
    }
}