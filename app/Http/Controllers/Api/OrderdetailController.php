<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderdetailRequest;
use App\Http\Resources\OrderdetailResource;
use App\Services\OrderdetailService;

class OrderdetailController extends Controller
{
    protected $service;

    public function __construct(OrderdetailService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }


    //lấy tất cả 
    public function index()
    {
        return OrderdetailResource::collection($this->service->getAll());
    }
    //thêm

    public function store(StoreOrderdetailRequest $request)
    {
        $orderDetail = $this->service->create($request->validated());

        return new OrderdetailResource($orderDetail);
    }
   //hiện 1
    public function show($OrderDetailId)
    {
        $orderDetail = $this->service->find($OrderDetailId);
        if (!$orderDetail) {
            return response()->json(['message' => 'Order detail not found'], 404);
        }
        return new OrderdetailResource($orderDetail);
    }
  //cập nhật
    public function update(StoreOrderdetailRequest $request, $OrderDetailId)
    {
        $orderDetail = $this->service->update($OrderDetailId, $request->validated());
        if (!$orderDetail) {
            return response()->json(['message' => 'Order detail not found'], 404);
        }
        return new OrderdetailResource($orderDetail);
    }
   //xoá
    public function destroy($OrderDetailId)
    {
        $deleted = $this->service->delete($OrderDetailId);
        if (!$deleted) {
            return response()->json(['message' => 'Order detail not found'], 404);
        }
        return response()->json(['message' => 'Order detail deleted successfully']);
    }
}
