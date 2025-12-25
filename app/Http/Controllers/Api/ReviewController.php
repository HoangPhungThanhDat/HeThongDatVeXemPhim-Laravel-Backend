<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Services\ReviewService;

class ReviewController extends Controller
{
    protected $service;

    public function __construct(ReviewService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }


    //lấy tất cả 
    public function index()
    {
        return ReviewResource::collection($this->service->getAll());
    }
    //thêm

    public function store(StoreReviewRequest $request)
    {
        $review = $this->service->create($request->validated());

        return new ReviewResource($review);
    }
   //hiện 1
    public function show($ReviewId)
    {
        $review = $this->service->find($ReviewId);
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }
        return new ReviewResource($review);
    }
  //cập nhật
    public function update(StoreReviewRequest $request, $ReviewId)
    {
        $review = $this->service->update($ReviewId, $request->validated());
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }
        return new ReviewResource($review);
    }
   //xoá
    public function destroy($ReviewId)
    {
        $deleted = $this->service->delete($ReviewId);
        if (!$deleted) {
            return response()->json(['message' => 'Review not found'], 404);
        }
        return response()->json(['message' => 'Review deleted successfully']);
    }
}
