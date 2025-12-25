<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Controllers\Controller;
use App\Services\OrderService;

class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $orders = $this->orderService->getAll();
        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->create($request->validated());
        return new OrderResource($order);
    }

    public function show($OrderId)
    {
        $order = $this->orderService->getById($OrderId);
        return new OrderResource($order);
    }

    public function update(StoreOrderRequest $request, $OrderId)
    {
        $order = $this->orderService->update($OrderId, $request->validated());
        return new OrderResource($order);
    }

    public function destroy($OrderId)
    {
        $this->orderService->delete($OrderId);
        return response()->json(['message' => 'Order deleted successfully']);
    }
}
