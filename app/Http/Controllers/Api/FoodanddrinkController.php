<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFoodanddrinkRequest;
use App\Http\Resources\FoodanddrinkResource;
use App\Services\FoodanddrinkService;

class FoodanddrinkController extends Controller
{
    protected $service;

    public function __construct(FoodanddrinkService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }


    //lấy tất cả 
    public function index()
    {
        return FoodanddrinkResource::collection($this->service->getAll());
    }
    //thêm

    public function store(StoreFoodanddrinkRequest $request)
    {
        $foodanddrink = $this->service->create($request->validated());
        
        return new FoodanddrinkResource($foodanddrink);
    }
   //hiện 1
    public function show($ItemId)
    {
        $foodanddrink = $this->service->find($ItemId);
        if (!$foodanddrink) {
            return response()->json(['message' => 'Food and drink item not found'], 404);
        }
        return new FoodanddrinkResource($foodanddrink);
    }
  //cập nhật
    public function update(StoreFoodanddrinkRequest $request, $ItemId)
    {
        $foodanddrink = $this->service->update($ItemId, $request->validated());
        if (!$foodanddrink) {
            return response()->json(['message' => 'Food and drink item not found'], 404);
        }
        return new FoodanddrinkResource($foodanddrink);
    }
   //xoá
    public function destroy($ItemId)
    {
        $deleted = $this->service->delete($ItemId);
        if (!$deleted) {
            return response()->json(['message' => 'Audit log not found'], 404);
        }
        return response()->json(['message' => 'Audit log deleted successfully']);
    }
}
