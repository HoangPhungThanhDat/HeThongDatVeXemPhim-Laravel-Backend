<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderdetailRequest;
use App\Http\Resources\OrderdetailResource;
use App\Services\OrderdetailService;

/**
 * @group Order Details
 * 
 * APIs for managing order details (line items within an order).
 * Each order detail represents a ticket or a food/drink item in the order.
 */
class OrderdetailController extends Controller
{
    protected $service;

    public function __construct(OrderdetailService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }

    /**
     * Get all order details
     * 
     * Get a list of all order details (line items) in the system. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "OrderDetailId": 1,
     *     "OrderId": 1,
     *     "TicketId": 1,
     *     "ItemId": null,
     *     "Quantity": 1,
     *     "Price": 120000,
     *     "SubTotal": 120000,
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   }
     * ]
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Forbidden - Admin only"
     * }
     */
    public function index()
    {
        return OrderdetailResource::collection($this->service->getAll());
    }

    /**
     * Create a new order detail
     * 
     * Add a new line item to an order (ticket or food/drink). (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam OrderId int required The ID of the order. Example: 1
     * @bodyParam TicketId int The ID of the ticket (if this is a ticket item). Example: 1
     * @bodyParam ItemId int The ID of the food/drink item (if this is a food item). Example: 1
     * @bodyParam Quantity int required The quantity. Example: 1
     * @bodyParam Price numeric required The price per unit. Example: 120000
     * 
     * @response 201 {
     *   "OrderDetailId": 1,
     *   "OrderId": 1,
     *   "TicketId": 1,
     *   "ItemId": null,
     *   "Quantity": 1,
     *   "Price": 120000,
     *   "SubTotal": 120000,
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "OrderId": ["The order id field is required."],
     *     "Quantity": ["The quantity field is required."],
     *     "Price": ["The price field is required."]
     *   }
     * }
     */
    public function store(StoreOrderdetailRequest $request)
    {
        $orderDetail = $this->service->create($request->validated());
        return new OrderdetailResource($orderDetail);
    }

    /**
     * Get a specific order detail
     * 
     * Get detailed information about a specific order detail (line item). (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam OrderDetailId int required The ID of the order detail. Example: 1
     * 
     * @response 200 {
     *   "OrderDetailId": 1,
     *   "OrderId": 1,
     *   "TicketId": 1,
     *   "ItemId": null,
     *   "Quantity": 1,
     *   "Price": 120000,
     *   "SubTotal": 120000,
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Order detail not found"
     * }
     */
    public function show($OrderDetailId)
    {
        $orderDetail = $this->service->find($OrderDetailId);
        if (!$orderDetail) {
            return response()->json(['message' => 'Order detail not found'], 404);
        }
        return new OrderdetailResource($orderDetail);
    }

    /**
     * Update an order detail
     * 
     * Update an existing order detail (line item). (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam OrderDetailId int required The ID of the order detail. Example: 1
     * @bodyParam OrderId int The ID of the order. Example: 1
     * @bodyParam TicketId int The ID of the ticket. Example: 1
     * @bodyParam ItemId int The ID of the food/drink item. Example: 1
     * @bodyParam Quantity int The quantity. Example: 2
     * @bodyParam Price numeric The price per unit. Example: 120000
     * 
     * @response 200 {
     *   "OrderDetailId": 1,
     *   "OrderId": 1,
     *   "TicketId": 1,
     *   "ItemId": null,
     *   "Quantity": 2,
     *   "Price": 120000,
     *   "SubTotal": 240000,
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Order detail not found"
     * }
     */
    public function update(StoreOrderdetailRequest $request, $OrderDetailId)
    {
        $orderDetail = $this->service->update($OrderDetailId, $request->validated());
        if (!$orderDetail) {
            return response()->json(['message' => 'Order detail not found'], 404);
        }
        return new OrderdetailResource($orderDetail);
    }

    /**
     * Delete an order detail
     * 
     * Remove a line item from an order. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam OrderDetailId int required The ID of the order detail. Example: 1
     * 
     * @response 200 {
     *   "message": "Order detail deleted successfully"
     * }
     * @response 404 {
     *   "message": "Order detail not found"
     * }
     */
    public function destroy($OrderDetailId)
    {
        $deleted = $this->service->delete($OrderDetailId);
        if (!$deleted) {
            return response()->json(['message' => 'Order detail not found'], 404);
        }
        return response()->json(['message' => 'Order detail deleted successfully']);
    }
}