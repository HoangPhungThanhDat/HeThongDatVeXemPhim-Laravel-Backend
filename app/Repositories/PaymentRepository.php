<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\Orderdetail;
use App\Models\Showtimeseat;
use Illuminate\Support\Facades\DB;

class PaymentRepository
{
    /**
     * Lấy danh sách payment cho admin (kèm quan hệ)
     */
    public function getAllWithRelations()
    {
        return Payment::with([
            'ticket.user',
            'ticket.showtime.movie',
            'ticket.seat.room.cinema',
            'ticket.orderdetails',
        ])->orderBy('PaymentId', 'desc')->get();
    }

    /**
     * Tìm order theo id
     */
    public function findOrder(int $orderId): ?Order
    {
        return Order::find($orderId);
    }

    /**
     * Tạo order mới
     */
    public function createOrder(array $data): Order
    {
        return Order::create($data);
    }

    /**
     * Tạo ticket mới
     */
    public function createTicket(array $data): Ticket
    {
        return Ticket::create($data);
    }

    /**
     * Tạo order detail
     */
    public function createOrderDetail(array $data): Orderdetail
    {
        return Orderdetail::create($data);
    }

    /**
     * Cập nhật trạng thái ghế trong suất chiếu
     */
    public function updateShowtimeSeatStatus(int $showtimeId, int $seatId, string $status): void
    {
        Showtimeseat::where('ShowtimeId', $showtimeId)
            ->where('SeatId', $seatId)
            ->update(['Status' => $status]);
    }

    /**
     * Lấy tất cả order detail theo order id
     */
    public function getOrderDetailsByOrderId(int $orderId)
    {
        return Orderdetail::where('OrderId', $orderId)->get();
    }

    /**
     * Tìm ticket theo id
     */
    public function findTicket(int $ticketId): ?Ticket
    {
        return Ticket::find($ticketId);
    }

    /**
     * Cập nhật ticket
     */
    public function updateTicket(Ticket $ticket, array $data): bool
    {
        return $ticket->update($data);
    }

    /**
     * Cập nhật order
     */
    public function updateOrder(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    /**
     * Tạo hoặc cập nhật payment (chống lặp dữ liệu)
     */
    public function updateOrCreatePayment(int $ticketId, array $data): Payment
    {
        return Payment::updateOrCreate(
            ['TicketId' => $ticketId],
            $data
        );
    }

    /**
     * Lấy showtime theo id
     */
    public function findShowtime(int $showtimeId)
    {
        return \App\Models\Showtime::find($showtimeId);
    }

    // ========== Transaction helpers ==========
    public function beginTransaction(): void
    {
        DB::beginTransaction();
    }

    public function commit(): void
    {
        DB::commit();
    }

    public function rollBack(): void
    {
        DB::rollBack();
    }
}