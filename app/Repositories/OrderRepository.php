<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;

class OrderRepository
{
    // Cache rất ngắn (30s) vì đơn hàng thay đổi liên tục (khách đặt vé, thanh toán...)
    const CACHE_TIME = 30; 

    public function getAllFormatted()
    {
        return Cache::remember('orders_all_formatted', self::CACHE_TIME, function () {
            $orders = Order::with([
                'user',
                'promotion',
                'orderDetails.ticket.showtime.movie',
                'orderDetails.ticket.showtime.room.cinema',
                'orderDetails.ticket.seat',
                'orderDetails.foodanddrink'
            ])->orderBy('OrderDate', 'desc')->get();

            return $orders->map(function ($order) {
                $tickets = [];
                $foods = [];
                $seats = [];

                foreach ($order->orderDetails as $detail) {
                    if ($detail->ticket) {
                        $t = $detail->ticket;
                        $tickets[] = $t;
                        if ($t->seat) {
                            $seats[] = $t->seat->Row . $t->seat->Number;
                        }
                    }
                    if ($detail->foodanddrink) {
                        $foods[] = $detail->foodanddrink;
                    }
                }

                $firstTicket = $tickets[0] ?? null;
                $showtime = $firstTicket->showtime ?? null;
                $movie = $showtime->movie ?? null;
                $room = $showtime->room ?? null;
                $cinema = $room->cinema ?? null;

                return [
                    'OrderId' => $order->OrderId,
                    'OrderDate' => $order->OrderDate,
                    'TotalAmount' => $order->TotalAmount,
                    'Status' => $order->Status,
                    'PaymentStatus' => $order->Status === 'Paid' ? 'Success' : 'Pending',
                    'PaymentMethod' => 'Chuyển khoản',
                    'Note' => $order->Note,
                    'refundStatus' => $order->RefundStatus ?? null,
                    'refundReason' => $order->RefundNote ?? '',
                    'User' => [
                        'FullName' => $order->user->FullName ?? 'Khách hàng',
                        'Email' => $order->user->Email ?? '',
                        'PhoneNumber' => $order->user->PhoneNumber ?? 'Chưa có SĐT',
                    ],
                    'Movie' => $movie ? ['Title' => $movie->Title] : null,
                    'Cinema' => $cinema ? ['Name' => $cinema->Name] : null,
                    'Room' => $room ? ['Name' => $room->Name, 'RoomType' => $room->RoomType] : null,
                    'Showtime' => $showtime ? ['StartTime' => $showtime->StartTime] : null,
                    'Seats' => $seats,
                    'Tickets' => $tickets,
                    'Foods' => $foods,
                ];
            });
        });
    }

    public function getById($id)
    {
        return Cache::remember("order_detail_{$id}", self::CACHE_TIME, function () use ($id) {
            return Order::findOrFail($id);
        });
    }

    public function create(array $data)
    {
        $order = Order::create($data);
        $this->clearCache();
        return $order;
    }

    public function update($id, array $data)
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        $this->clearCache($id);
        return $order;
    }

    public function delete($id)
    {
        $order = Order::findOrFail($id);
        $result = $order->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * Hàm lấy chi tiết đơn hàng đã được format (chuyển từ Controller xuống)
     */
        public function getOrderDetails($OrderId)
    {
        // Sử dụng select() để chỉ lấy các cột cần thiết, giảm dung lượng JSON
        $order = Order::with([
            'user:UserId,FullName,Email,PhoneNumber',
            'promotion:PromotionId,Code,Title,DiscountValue,DiscountType',
            'orderDetails' => function($q) {
                // Chỉ lấy các cột cần thiết của bảng orderdetails
                $q->select('OrderDetailId', 'OrderId', 'TicketId', 'ItemId', 'Price', 'Quantity');
            },
            'orderDetails.ticket' => function($q) {
                $q->select('TicketId', 'ShowtimeId', 'SeatId', 'Status', 'BookingTime');
            },
            'orderDetails.ticket.showtime' => function($q) {
                $q->select('ShowtimeId', 'MovieId', 'RoomId', 'StartTime', 'EndTime', 'Price');
            },
            'orderDetails.ticket.showtime.movie' => function($q) {
                $q->select('MovieId', 'Title', 'Duration', 'PosterUrl');
            },
            'orderDetails.ticket.showtime.room' => function($q) {
                $q->select('RoomId', 'Name', 'RoomType', 'CinemaId');
            },
            'orderDetails.ticket.showtime.room.cinema' => function($q) {
                $q->select('CinemaId', 'Name', 'Address', 'City', 'Phone');
            },
            'orderDetails.ticket.seat' => function($q) {
                $q->select('SeatId', 'Row', 'Number', 'SeatType');
            },
            'orderDetails.foodanddrink' => function($q) {
                $q->select('ItemId', 'Name', 'Price', 'ImageUrl');
            }
        ])->find($OrderId);

        if (!$order) {
            return null;
        }

        $data = [
            'OrderId' => $order->OrderId,
            'OrderDate' => $order->OrderDate ? date('Y-m-d H:i:s', strtotime($order->OrderDate)) : null,
            'TotalAmount' => $order->TotalAmount,
            'Status' => $order->Status,
            'customer' => [
                'UserId' => $order->user->UserId ?? null,
                'FullName' => $order->user->FullName ?? 'Khách hàng không xác định',
                'Email' => $order->user->Email ?? null,
                'PhoneNumber' => $order->user->PhoneNumber ?? null,
            ],
            'promotion' => $order->promotion ? [
                'Code' => $order->promotion->Code,
                'Title' => $order->promotion->Title,
                'DiscountValue' => $order->promotion->DiscountValue,
            ] : null,
            'tickets' => [],
            'food_and_drinks' => []
        ];

        $subTotal = 0;
        foreach ($order->orderDetails as $detail) {
            // ⭐ BƯỚC SỬA LỖI 500: Kiểm tra null an toàn
            if ($detail->TicketId && $detail->ticket) {
                $ticket = $detail->ticket;
                $showtime = $ticket->showtime;
                $movie = $showtime ? $showtime->movie : null;
                $room = $showtime ? $showtime->room : null;
                $cinema = $room ? $room->cinema : null;

                $subTotal += $detail->Price;

                $data['tickets'][] = [
                    'TicketId' => $ticket->TicketId,
                    'Price' => $detail->Price,
                    'Status' => $ticket->Status,
                    'BookingTime' => $ticket->BookingTime ? date('Y-m-d H:i:s', strtotime($ticket->BookingTime)) : null,
                    'showtime' => $showtime ? [
                        'StartTime' => $showtime->StartTime ? date('Y-m-d H:i:s', strtotime($showtime->StartTime)) : null,
                        'EndTime' => $showtime->EndTime ? date('Y-m-d H:i:s', strtotime($showtime->EndTime)) : null,
                        'Price' => $showtime->Price,
                    ] : null,
                    'movie' => $movie ? [
                        'MovieId' => $movie->MovieId,
                        'Title' => $movie->Title,
                        'Duration' => $movie->Duration,
                        'PosterUrl' => $movie->PosterUrl,
                    ] : null,
                    'seat' => $ticket->seat ? [
                        'SeatId' => $ticket->seat->SeatId,
                        'Row' => $ticket->seat->Row,
                        'Number' => $ticket->seat->Number,
                        'SeatType' => $ticket->seat->SeatType,
                    ] : null,
                    'room' => $room ? [
                        'RoomId' => $room->RoomId,
                        'Name' => $room->Name,
                        'RoomType' => $room->RoomType,
                    ] : null,
                    'cinema' => $cinema ? [
                        'CinemaId' => $cinema->CinemaId,
                        'Name' => $cinema->Name,
                        'Address' => $cinema->Address,
                        'City' => $cinema->City,
                        'Phone' => $cinema->Phone,
                    ] : null
                ];
            }

            if ($detail->ItemId && $detail->foodanddrink) {
                $subTotal += $detail->Price * $detail->Quantity;
                $data['food_and_drinks'][] = [
                    'ItemId' => $detail->foodanddrink->ItemId,
                    'Name' => $detail->foodanddrink->Name,
                    'Price' => $detail->foodanddrink->Price,
                    'Quantity' => $detail->Quantity,
                    'TotalPrice' => $detail->Price * $detail->Quantity,
                    'ImageUrl' => $detail->foodanddrink->ImageUrl,
                ];
            }
        }

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

        return $data;
    }

    public function getMyOrders($userId, $limit = 10)
    {
        $orders = Order::with([
            'user',
            'promotion',
            'orderDetails.ticket.showtime.movie',
            'orderDetails.ticket.seat',
            'orderDetails.foodanddrink'
        ])
            ->where('UserId', $userId)
            ->orderBy('OrderDate', 'desc')
            ->paginate($limit);

        $result = [];
        foreach ($orders->items() as $order) {
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

        return [
            'data' => $result,
            'pagination' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ]
        ];
    }

    public function checkOrderOwnership($orderId, $userId)
    {
        return Order::where('OrderId', $orderId)->where('UserId', $userId)->exists();
    }

    public function getUserOrders($userId)
    {
        $orders = Order::with([
            'user',
            'promotion',
            'orderDetails.ticket.showtime.movie',
            'orderDetails.ticket.seat',
            'orderDetails.foodanddrink'
        ])
            ->where('UserId', $userId)
            ->orderBy('OrderDate', 'desc')
            ->get();

        if ($orders->isEmpty()) {
            return null;
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

        return $result;
    }

    public function clearCache($id = null)
    {
        Cache::forget('orders_all_formatted');
        if ($id) {
            Cache::forget("order_detail_{$id}");
        }
    }
}