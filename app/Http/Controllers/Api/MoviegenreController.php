<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMoviegenreRequest;
use App\Http\Resources\MoviegenreResource;
use App\Services\MoviegenreService;

class MoviegenreController extends Controller
{
    protected $service;

    public function __construct(MoviegenreService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }


    //lấy tất cả 
    public function index()
    {
        return MoviegenreResource::collection($this->service->getAll());
    }
    //thêm

    public function store(StoreMoviegenreRequest $request)
    {
        $moviegenre = $this->service->create($request->validated());

        return new MoviegenreResource($moviegenre);
    }
   //hiện 1
    public function show($MovieGenreId)
    {
        $moviegenre = $this->service->find($MovieGenreId);
        if (!$moviegenre) {
            return response()->json(['message' => 'Movie genre not found'], 404);
        }
        return new MoviegenreResource($moviegenre);
    }
  //cập nhật
    public function update(StoreMoviegenreRequest $request, $MovieGenreId)
    {
        $moviegenre = $this->service->update($MovieGenreId, $request->validated());
        if (!$moviegenre) {
            return response()->json(['message' => 'Movie genre not found'], 404);
        }
        return new MoviegenreResource($moviegenre);
    }
   //xoá
    public function destroy($MovieGenreId)
    {
        $deleted = $this->service->delete($MovieGenreId);
        if (!$deleted) {
            return response()->json(['message' => 'Movie genre not found'], 404);
        }
        return response()->json(['message' => 'Movie genre deleted successfully']);
    }
}
