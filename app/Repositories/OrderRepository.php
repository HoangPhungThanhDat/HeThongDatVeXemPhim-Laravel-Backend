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
        // Không cache hàm này vì nó tính toán phức tạp và cần độ chính xác cao
        $order = Order::with([
            'user',
            'promotion',
            'orderDetails.ticket.showtime.movie',
            'orderDetails.ticket.showtime.room.cinema',
            'orderDetails.ticket.seat',
            'orderDetails.foodanddrink'
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
            if ($detail->TicketId && $detail->ticket) {
                $ticket = $detail->ticket;
                $showtime = $ticket->showtime;

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
                    'movie' => $showtime && $showtime->movie ? [
                        'MovieId' => $showtime->movie->MovieId,
                        'Title' => $showtime->movie->Title,
                        'Duration' => $showtime->movie->Duration,
                        'PosterUrl' => $showtime->movie->PosterUrl,
                    ] : null,
                    'seat' => $ticket->seat ? [
                        'SeatId' => $ticket->seat->SeatId,
                        'Row' => $ticket->seat->Row,
                        'Number' => $ticket->seat->Number,
                        'SeatType' => $ticket->seat->SeatType,
                    ] : null,
                    'room' => $showtime && $showtime->room ? [
                        'RoomId' => $showtime->room->RoomId,
                        'Name' => $showtime->room->Name,
                        'RoomType' => $showtime->room->RoomType,
                    ] : null,
                    'cinema' => $showtime && $showtime->room && $showtime->room->cinema ? [
                        'CinemaId' => $showtime->room->cinema->CinemaId,
                        'Name' => $showtime->room->cinema->Name,
                        'Address' => $showtime->room->cinema->Address,
                        'City' => $showtime->room->cinema->City,
                        'Phone' => $showtime->room->cinema->Phone,
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