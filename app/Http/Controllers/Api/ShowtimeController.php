<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreShowtimeRequest;
use App\Http\Resources\ShowtimeResource;
use App\Services\ShowtimeService;

class ShowtimeController extends Controller
{
    protected $service;

    public function __construct(ShowtimeService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }


    //lấy tất cả 
    public function index()
    {
        return ShowtimeResource::collection($this->service->getAll());
    }
    //thêm

    public function store(StoreShowtimeRequest $request)
    {
        $showtime = $this->service->create($request->validated());

        return new ShowtimeResource($showtime);
    }
   //hiện 1
    public function show($ShowtimeId)
    {
        $showtime = $this->service->find($ShowtimeId);
        if (!$showtime) {
            return response()->json(['message' => 'Showtime not found'], 404);
        }
        return new ShowtimeResource($showtime);
    }
  //cập nhật
    public function update(StoreShowtimeRequest $request, $ShowtimeId)
    {
        $showtime = $this->service->update($ShowtimeId, $request->validated());
        if (!$showtime) {
            return response()->json(['message' => 'Showtime not found'], 404);
        }
        return new ShowtimeResource($showtime);
    }
   //xoá
    public function destroy($ShowtimeId)
    {
        $deleted = $this->service->delete($ShowtimeId);
        if (!$deleted) {
            return response()->json(['message' => 'Showtime not found'], 404);
        }
        return response()->json(['message' => 'Showtime deleted successfully']);
    }
}
