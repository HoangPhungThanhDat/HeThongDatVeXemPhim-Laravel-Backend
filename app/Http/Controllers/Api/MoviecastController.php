<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMoviecastRequest;
use App\Http\Resources\MoviecastResource;
use App\Services\MoviecastService;

class MoviecastController extends Controller
{
    protected $service;

    public function __construct(MoviecastService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }


    //lấy tất cả 
    public function index()
    {
        return MoviecastResource::collection($this->service->getAll());
    }
    //thêm

    public function store(StoreMoviecastRequest $request)
    {
        $moviecast = $this->service->create($request->validated());

        return new MoviecastResource($moviecast);
    }
   //hiện 1
    public function show($CastId)
    {
        $moviecast = $this->service->find($CastId);
        if (!$moviecast) {
            return response()->json(['message' => 'Movie cast not found'], 404);
        }
        return new MoviecastResource($moviecast);
    }
  //cập nhật
    public function update(StoreMoviecastRequest $request, $CastId)
    {
        $moviecast = $this->service->update($CastId, $request->validated());
        if (!$moviecast) {
            return response()->json(['message' => 'Movie cast not found'], 404);
        }
        return new MoviecastResource($moviecast);
    }
   //xoá
    public function destroy($CastId)
    {
        $deleted = $this->service->delete($CastId);
        if (!$deleted) {
            return response()->json(['message' => 'Movie cast not found'], 404);
        }
        return response()->json(['message' => 'Movie cast deleted successfully']);
    }
}
