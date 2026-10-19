<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Http\Controllers\Controller;
use App\Services\OrderService;

/**
 * @group Orders
 * 
 * APIs for managing orders (ticket and food bookings).
 * Orders contain tickets, seats, showtime information, and food & drinks.
 */
class OrderController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->middleware(['auth:api', 'checkrole:Admin'])->only(['store', 'update', 'destroy']);
    }

    /**
     * Get all orders
     * 
     * Get a list of all orders. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "OrderId": 1,
     *     "UserId": 1,
     *     "OrderDate": "2024-01-01 10:00:00",
     *     "TotalAmount": 150000,
     *     "Status": "Paid",
     *     "User": {
     *       "FullName": "Nguyễn Văn A",
     *       "Email": "admin@gauphim.com"
     *     }
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
        $result = $this->orderService->getAllFormatted();
        return response()->json(['data' => $result]);
    }

    /**
     * Create a new order
     * 
     * Create a new order with tickets and food items. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam UserId int required The ID of the user. Example: 1
     * @bodyParam PromotionId int The promotion ID if applied. Example: 1
     * @bodyParam TotalAmount numeric required The total amount. Example: 150000
     * @bodyParam Status string The order status. Enum: Pending, Paid, Cancelled. Example: Paid
     * @bodyParam OrderDate string The order date. Example: 2024-01-01 10:00:00
     * 
     * @response 201 {
     *   "OrderId": 1,
     *   "UserId": 1,
     *   "OrderDate": "2024-01-01 10:00:00",
     *   "TotalAmount": 150000,
     *   "Status": "Paid"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "UserId": ["The user id field is required."],
     *     "TotalAmount": ["The total amount field is required."]
     *   }
     * }
     */
    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->create($request->validated());
        return new OrderResource($order);
    }

    /**
     * Get a specific order
     * 
     * Get detailed information about a specific order. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam OrderId int required The ID of the order. Example: 1
     * 
     * @response 200 {
     *   "OrderId": 1,
     *   "UserId": 1,
     *   "OrderDate": "2024-01-01 10:00:00",
     *   "TotalAmount": 150000,
     *   "Status": "Paid"
     * }
     * @response 404 {
     *   "message": "Order not found"
     * }
     */
    public function show($OrderId)
    {
        $order = $this->orderService->getById($OrderId);
        return new OrderResource($order);
    }

    /**
     * Update an order
     * 
     * Update an existing order. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam OrderId int required The ID of the order. Example: 1
     * @bodyParam UserId int The ID of the user. Example: 1
     * @bodyParam TotalAmount numeric The total amount. Example: 150000
     * @bodyParam Status string The order status. Enum: Pending, Paid, Cancelled. Example: Cancelled
     * 
     * @response 200 {
     *   "OrderId": 1,
     *   "UserId": 1,
     *   "TotalAmount": 150000,
     *   "Status": "Cancelled"
     * }
     * @response 404 {
     *   "message": "Order not found"
     * }
     */
    public function update(StoreOrderRequest $request, $OrderId)
    {
        $order = $this->orderService->update($OrderId, $request->validated());
        return new OrderResource($order);
    }

    /**
     * Delete an order
     * 
     * Delete an order from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam OrderId int required The ID of the order. Example: 1
     * 
     * @response 200 {
     *   "message": "Order deleted successfully"
     * }
     * @response 404 {
     *   "message": "Order not found"
     * }
     */
    public function destroy($OrderId)
    {
        $this->orderService->delete($OrderId);
        return response()->json(['message' => 'Order deleted successfully']);
    }

    /**
     * Get order details with full information
     * 
     * Get comprehensive order details including customer info, cinema, room,
     * showtime, tickets, seats, and food & drinks.
     * 
     * @authenticated
     * 
     * @urlParam OrderId int required The ID of the order. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "OrderId": 1,
     *     "OrderDate": "2024-01-01 10:00:00",
     *     "TotalAmount": 150000,
     *     "Status": "Paid",
     *     "customer": {
     *       "UserId": 1,
     *       "FullName": "Nguyễn Văn A",
     *       "Email": "admin@gauphim.com",
     *       "PhoneNumber": "0987654321"
     *     },
     *     "tickets": [...],
     *     "food_and_drinks": [...],
     *     "summary": {
     *       "sub_total": 150000,
     *       "discount": 0,
     *       "final_total": 150000,
     *       "total_tickets": 1,
     *       "total_food_items": 1
     *     }
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy đơn hàng"
     * }
     */
    public function getOrderDetails($OrderId)
    {
        $data = $this->orderService->getOrderDetails($OrderId);
        
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }

    /**
     * Get current user's orders
     * 
     * Get all orders of the authenticated user.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Vui lòng đăng nhập"
     * }
     */
    public function getMyOrders(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        $limit = (int) $request->query('limit', 10);
        $result = $this->orderService->getMyOrders($user->UserId, $limit);

        return response()->json([
            'success' => true,
            'data' => $result['data'],
            'pagination' => $result['pagination']
        ]);
    }

    /**
     * Get current user's order details
     * 
     * Get detailed information of a specific order belonging to the authenticated user.
     * 
     * @authenticated
     * 
     * @urlParam OrderId int required The ID of the order. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "data": { ... full order details ... }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Vui lòng đăng nhập"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy đơn hàng"
     * }
     */
    public function getMyOrderDetails($OrderId)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        // Kiểm tra quyền sở hữu đơn hàng
        $isOwner = $this->orderService->checkOrderOwnership($OrderId, $user->UserId);
        if (!$isOwner) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }

        // Tái sử dụng hàm getOrderDetails
        return $this->getOrderDetails($OrderId);
    }

    /**
     * Get orders by user ID
     * 
     * Get all orders of a specific user. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam UserId int required The ID of the user. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy đơn hàng"
     * }
     */
    public function getUserOrders($UserId)
    {
        $result = $this->orderService->getUserOrders($UserId);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}