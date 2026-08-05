<?php

namespace App\Http\Controllers\Api;

use App\Models\Order;
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
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store', 'update', 'destroy']);
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
        $orders = $this->orderService->getAll();
        return OrderResource::collection($orders);
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
     *     "tickets": [
     *       {
     *         "TicketId": 1,
     *         "Price": 120000,
     *         "showtime": {
     *           "StartTime": "2024-01-01 10:00:00",
     *           "EndTime": "2024-01-01 13:00:00"
     *         },
     *         "movie": {
     *           "MovieId": 1,
     *           "Title": "Avengers: Endgame"
     *         },
     *         "seat": {
     *           "SeatId": 1,
     *           "Row": "A",
     *           "Number": 1
     *         },
     *         "cinema": {
     *           "CinemaId": 1,
     *           "Name": "CGV Vincom Center"
     *         }
     *       }
     *     ],
     *     "food_and_drinks": [
     *       {
     *         "ItemId": 1,
     *         "Name": "Bắp rang bơ",
     *         "Price": 30000,
     *         "Quantity": 1,
     *         "TotalPrice": 30000
     *       }
     *     ],
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
        // Lấy order với tất cả quan hệ
        $order = Order::with([
            'user',
            'promotion',
            'orderDetails.ticket.showtime.movie',
            'orderDetails.ticket.showtime.room.cinema',
            'orderDetails.ticket.seat',
            'orderDetails.foodanddrink'
        ])->find($OrderId);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }

        // Xây dựng dữ liệu trả về
        $data = [
            'OrderId' => $order->OrderId,
            'OrderDate' => $order->OrderDate ? date('Y-m-d H:i:s', strtotime($order->OrderDate)) : null,
            'TotalAmount' => $order->TotalAmount,
            'Status' => $order->Status,
            
            // 1. THÔNG TIN KHÁCH HÀNG
            'customer' => [
                'UserId' => $order->user->UserId ?? null,
                'FullName' => $order->user->FullName ?? 'Khách hàng không xác định',
                'Email' => $order->user->Email ?? null,
                'PhoneNumber' => $order->user->PhoneNumber ?? null,
            ],
            
            // 2. THÔNG TIN KHUYẾN MÃI
            'promotion' => $order->promotion ? [
                'Code' => $order->promotion->Code,
                'Title' => $order->promotion->Title,
                'DiscountValue' => $order->promotion->DiscountValue,
            ] : null,
            
            // 3. DANH SÁCH VÉ VÀ GHẾ
            'tickets' => [],
            
            // 4. DANH SÁCH ĐỒ UỐNG
            'food_and_drinks' => []
        ];

        // Xử lý từng OrderDetail
        $subTotal = 0;
        foreach ($order->orderDetails as $detail) {
            // Nếu là vé
            if ($detail->TicketId && $detail->ticket) {
                $ticket = $detail->ticket;
                $showtime = $ticket->showtime;
                
                $subTotal += $detail->Price;
                
                $ticketInfo = [
                    'TicketId' => $ticket->TicketId,
                    'Price' => $detail->Price,
                    'Status' => $ticket->Status,
                    'BookingTime' => $ticket->BookingTime ? date('Y-m-d H:i:s', strtotime($ticket->BookingTime)) : null,
                    
                    // Thông tin suất chiếu
                    'showtime' => $showtime ? [
                        'StartTime' => $showtime->StartTime ? date('Y-m-d H:i:s', strtotime($showtime->StartTime)) : null,
                        'EndTime' => $showtime->EndTime ? date('Y-m-d H:i:s', strtotime($showtime->EndTime)) : null,
                        'Price' => $showtime->Price,
                    ] : null,
                    
                    // Thông tin phim
                    'movie' => $showtime && $showtime->movie ? [
                        'MovieId' => $showtime->movie->MovieId,
                        'Title' => $showtime->movie->Title,
                        'Duration' => $showtime->movie->Duration,
                        'PosterUrl' => $showtime->movie->PosterUrl,
                    ] : null,
                    
                    // Thông tin GHẾ ĐÃ ĐẶT
                    'seat' => $ticket->seat ? [
                        'SeatId' => $ticket->seat->SeatId,
                        'Row' => $ticket->seat->Row,
                        'Number' => $ticket->seat->Number,
                        'SeatType' => $ticket->seat->SeatType,
                    ] : null,
                    
                    // Thông tin PHÒNG CHIẾU
                    'room' => $showtime && $showtime->room ? [
                        'RoomId' => $showtime->room->RoomId,
                        'Name' => $showtime->room->Name,
                        'RoomType' => $showtime->room->RoomType,
                    ] : null,
                    
                    // Thông tin RẠP CHIẾU
                    'cinema' => $showtime && $showtime->room && $showtime->room->cinema ? [
                        'CinemaId' => $showtime->room->cinema->CinemaId,
                        'Name' => $showtime->room->cinema->Name,
                        'Address' => $showtime->room->cinema->Address,
                        'City' => $showtime->room->cinema->City,
                        'Phone' => $showtime->room->cinema->Phone,
                    ] : null
                ];
                
                $data['tickets'][] = $ticketInfo;
            }
            
            // Nếu là đồ ăn/uống
            if ($detail->ItemId && $detail->foodanddrink) {
                $subTotal += $detail->Price * $detail->Quantity;
                
                $foodInfo = [
                    'ItemId' => $detail->foodanddrink->ItemId,
                    'Name' => $detail->foodanddrink->Name,
                    'Price' => $detail->foodanddrink->Price,
                    'Quantity' => $detail->Quantity,
                    'TotalPrice' => $detail->Price * $detail->Quantity,
                    'ImageUrl' => $detail->foodanddrink->ImageUrl,
                ];
                
                $data['food_and_drinks'][] = $foodInfo;
            }
        }

        // Tính tổng tiền
        $discount = 0;
        if ($order->promotion) {
            if ($order->promotion->DiscountType === 'Percentage') {
                $discount = ($subTotal * $order->promotion->DiscountValue) / 100;
            } else {
                $discount = $order->promotion->DiscountValue;
            }
        }

        $data['summary'] = [
            'sub_total' => $subTotal,
            'discount' => $discount,
            'final_total' => $subTotal - $discount,
            'total_tickets' => count($data['tickets']),
            'total_food_items' => count($data['food_and_drinks'])
        ];

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
     *   "data": [
     *     {
     *       "OrderId": 1,
     *       "OrderDate": "2024-01-01 10:00:00",
     *       "TotalAmount": 150000,
     *       "Status": "Paid",
     *       "customer": {
     *         "FullName": "Nguyễn Văn A"
     *       },
     *       "total_tickets": 2,
     *       "total_food_items": 1
     *     }
     *   ]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Vui lòng đăng nhập"
     * }
     */
    public function getMyOrders()
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập'
            ], 401);
        }

        $orders = Order::with([
            'user',
            'promotion',
            'orderDetails.ticket.showtime.movie',
            'orderDetails.ticket.seat',
            'orderDetails.foodanddrink'
        ])
        ->where('UserId', $user->UserId)
        ->orderBy('OrderDate', 'desc')
        ->get();

        $result = [];
        foreach ($orders as $order) {
            $result[] = [
                'OrderId' => $order->OrderId,
                'OrderDate' => $order->OrderDate ? date('Y-m-d H:i:s', strtotime($order->OrderDate)) : null,
                'TotalAmount' => $order->TotalAmount,
                'Status' => $order->Status,
                'customer' => [
                    'FullName' => $order->user->FullName ?? null,
                ],
                'total_tickets' => $order->orderDetails->whereNotNull('TicketId')->count(),
                'total_food_items' => $order->orderDetails->whereNotNull('ItemId')->count()
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result
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

        $order = Order::with([
            'user',
            'promotion',
            'orderDetails.ticket.showtime.movie',
            'orderDetails.ticket.showtime.room.cinema',
            'orderDetails.ticket.seat',
            'orderDetails.foodanddrink'
        ])
        ->where('UserId', $user->UserId)
        ->where('OrderId', $OrderId)
        ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }

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
        $orders = Order::with([
            'user',
            'promotion',
            'orderDetails.ticket.showtime.movie',
            'orderDetails.ticket.seat',
            'orderDetails.foodanddrink'
        ])
        ->where('UserId', $UserId)
        ->orderBy('OrderDate', 'desc')
        ->get();

        if ($orders->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy đơn hàng'
            ], 404);
        }

        $result = [];
        foreach ($orders as $order) {
            $result[] = [
                'OrderId' => $order->OrderId,
                'OrderDate' => $order->OrderDate ? date('Y-m-d H:i:s', strtotime($order->OrderDate)) : null,
                'TotalAmount' => $order->TotalAmount,
                'Status' => $order->Status,
                'customer' => [
                    'FullName' => $order->user->FullName ?? null,
                    'Email' => $order->user->Email ?? null,
                ],
                'total_tickets' => $order->orderDetails->whereNotNull('TicketId')->count()
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
}