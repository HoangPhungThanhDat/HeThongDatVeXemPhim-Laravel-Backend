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
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store', 'update', 'destroy']);
    }

    // Lấy danh sách đơn hàng
    public function index()
    {
        $orders = $this->orderService->getAll();
        return OrderResource::collection($orders);
    }

    // Tạo đơn hàng
    public function store(StoreOrderRequest $request)
    {
        $order = $this->orderService->create($request->validated());
        return new OrderResource($order);
    }

    // Lấy chi tiết 1 đơn hàng
    public function show($OrderId)
    {
        $order = $this->orderService->getById($OrderId);
        return new OrderResource($order);
    }

    // Cập nhật đơn hàng
    public function update(StoreOrderRequest $request, $OrderId)
    {
        $order = $this->orderService->update($OrderId, $request->validated());
        return new OrderResource($order);
    }

    // Xóa đơn hàng
    public function destroy($OrderId)
    {
        $this->orderService->delete($OrderId);
        return response()->json(['message' => 'Order deleted successfully']);
    }

    /**
     * API LẤY CHI TIẾT ĐƠN HÀNG - Quan trọng nhất
     * Lấy thông tin: tên khách hàng, rạp, phòng, suất chiếu, vé, ghế, đồ uống
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
     * API Lấy danh sách đơn hàng của user hiện tại
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
     * API Lấy chi tiết đơn hàng của user hiện tại
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
     * API Lấy danh sách đơn hàng của user (Admin)
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