<?php

namespace App\Repositories;

use App\Models\Ticket;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\FoodAndDrink;
use App\Models\Showtime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class TicketManagementRepository
{
    const CACHE_TIME = 300; // 5 phút

    public function getDashboardTickets()
    {
        return Cache::remember('ticket_management_dashboard', self::CACHE_TIME, function () {
            return Ticket::with([
                'user' => fn($q) => $q->select('UserId', 'FullName', 'Email', 'PhoneNumber'),
                // Phải select MovieId, RoomId ở bảng showtime thì Eloquent mới biết đường join
                'showtime' => fn($q) => $q->select('ShowtimeId', 'StartTime', 'EndTime', 'Price', 'RoomId', 'MovieId'),
                'showtime.room' => fn($q) => $q->select('RoomId', 'Name'),
                'showtime.movie' => fn($q) => $q->select('MovieId', 'Title', 'PosterUrl'),
                'seat' => fn($q) => $q->select('SeatId', 'Row', 'Number', 'SeatType'),
                'orderDetails' => fn($q) => $q->whereNotNull('ItemId')->with(['item' => fn($q2) => $q2->select('ItemId', 'Name', 'Price')])
            ])->orderBy('CreatedAt', 'desc')->limit(100)->get(); // Thêm limit(100) để tránh load cả ngàn vé
        });
    }

    public function getAvailableCombos()
    {
        return Cache::remember('ticket_management_combos', self::CACHE_TIME, function () {
            return FoodAndDrink::where('IsAvailable', true)
                ->orWhere('Status', 'Active')
                ->get(['ItemId', 'Name', 'Price', 'Description', 'ImageUrl']);
        });
    }

    public function getTicketDetail($ticketId)
    {
        return Ticket::with([
            'user',
            'showtime.movie',
            'showtime.room',
            'seat',
            'orderDetails.item',
            'orderDetails.order'
        ])->findOrFail($ticketId);
    }

    public function countTickets()
    {
        return Cache::remember('ticket_management_total_count', self::CACHE_TIME, function () {
            return Ticket::count();
        });
    }

    public function getTicketStatsByStatus()
    {
        return Cache::remember('ticket_management_stats', self::CACHE_TIME, function () {
            $total = Ticket::count();
            $statusLabels = [
                'Paid' => 'Đã thanh toán',
                'CheckedIn' => 'Đã check-in',
                'Pending' => 'Chờ xử lý',
                'Cancelled' => 'Đã hủy',
                'Expired' => 'Hết hạn'
            ];

            $statusData = [];
            foreach ($statusLabels as $key => $label) {
                $count = Ticket::where('Status', $key)->count();
                $statusData[] = [
                    'status' => $key,
                    'label' => $label,
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 2) : 0
                ];
            }
            return $statusData;
        });
    }

    public function getDailyTicketStats()
    {
        return Cache::remember('ticket_management_daily', self::CACHE_TIME, function () {
            return Ticket::select(
                DB::raw('DATE(CreatedAt) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN Status = "Paid" THEN 1 ELSE 0 END) as paid'),
                DB::raw('SUM(CASE WHEN Status = "Cancelled" THEN 1 ELSE 0 END) as cancelled')
            )
                ->where('CreatedAt', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->get();
        });
    }

    public function storeTickets(array $data)
    {
        DB::beginTransaction();
        try {
            $userId = $data['userId'];
            $showtimeId = $data['showtimeId'];
            $status = $data['status'] ?? 'Paid';
            $note = $data['note'] ?? '';
            $comboId = $data['comboId'] ?? null;
            $seats = $data['seats'];

            $showtime = Showtime::with(['movie', 'room'])->find($showtimeId);
            if (!$showtime) throw new \Exception('Không tìm thấy suất chiếu');

            $ticketPrice = $showtime->Price ?? 0;
            $comboPrice = 0;
            if ($comboId) {
                $combo = FoodAndDrink::find($comboId);
                $comboPrice = $combo?->Price ?? 0;
            }

            $totalAmount = ($ticketPrice * count($seats)) + $comboPrice;

            $order = Order::create([
                'UserId' => $userId,
                'OrderDate' => now(),
                'TotalAmount' => $totalAmount,
                'Status' => 'Paid',
                'Note' => $note,
            ]);

            $createdTickets = [];
            foreach ($seats as $seatData) {
                $existingTicket = Ticket::where('ShowtimeId', $showtimeId)
                    ->where('SeatId', $seatData['seatId'])
                    ->whereIn('Status', ['Paid', 'CheckedIn', 'Pending'])
                    ->first();

                if ($existingTicket) throw new \Exception("Ghế đã được đặt, vui lòng chọn ghế khác");

                $ticket = Ticket::create([
                    'ShowtimeId' => $showtimeId,
                    'SeatId' => $seatData['seatId'],
                    'UserId' => $userId,
                    'BookingTime' => now(),
                    'Status' => $status,
                    'Note' => $note,
                    'QRCode' => 'QR-' . uniqid(),
                ]);

                if (isset($seatData['showtimeSeatId'])) {
                    DB::table('showtimeseats')->where('Id', $seatData['showtimeSeatId'])->update(['Status' => 'Reserved']);
                }

                OrderDetail::create([
                    'OrderId' => $order->OrderId,
                    'TicketId' => $ticket->TicketId,
                    'ItemId' => null,
                    'Quantity' => 1,
                    'Price' => $ticketPrice,
                    'Status' => 'Active',
                ]);

                $createdTickets[] = [
                    'TicketId' => $ticket->TicketId,
                    'SeatLabel' => $seatData['seatLabel'] ?? 'A1',
                ];
            }

            if ($comboId && !empty($createdTickets)) {
                $firstTicketId = $createdTickets[0]['TicketId'];
                OrderDetail::create([
                    'OrderId' => $order->OrderId,
                    'TicketId' => $firstTicketId,
                    'ItemId' => $comboId,
                    'Quantity' => 1,
                    'Price' => $comboPrice,
                    'Status' => 'Active',
                ]);
            }

            DB::commit();
            $this->clearCache();

            $ticketIds = array_column($createdTickets, 'TicketId');
            $tickets = Ticket::with(['user', 'showtime.movie', 'seat'])->whereIn('TicketId', $ticketIds)->get();

            return [
                'order_id' => $order->OrderId,
                'tickets' => $tickets,
                'total_amount' => $totalAmount,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateTicket($ticketId, array $data)
    {
        DB::beginTransaction();
        try {
            $ticket = Ticket::findOrFail($ticketId);
            $oldSeatId = $ticket->SeatId;

            $ticket->update([
                'UserId' => $data['userId'] ?? $ticket->UserId,
                'ShowtimeId' => $data['showtimeId'] ?? $ticket->ShowtimeId,
                'SeatId' => $data['seatId'] ?? $ticket->SeatId,
                'Status' => $data['status'] ?? $ticket->Status,
                'Note' => $data['note'] ?? $ticket->Note,
            ]);

            if (isset($data['seatId']) && $data['seatId'] != $oldSeatId) {
                if (isset($data['oldShowtimeSeatId'])) {
                    DB::table('showtimeseats')->where('Id', $data['oldShowtimeSeatId'])->update(['Status' => 'Available']);
                }
                if (isset($data['newShowtimeSeatId'])) {
                    DB::table('showtimeseats')->where('Id', $data['newShowtimeSeatId'])->update(['Status' => 'Reserved']);
                }
            }

            if (array_key_exists('comboId', $data)) {
                $orderDetail = OrderDetail::where('TicketId', $ticketId)->whereNotNull('ItemId')->first();

                if ($orderDetail) {
                    $orderDetail->update(['ItemId' => $data['comboId']]);
                } else if ($data['comboId']) {
                    $order = Order::where('UserId', $ticket->UserId)->where('Status', 'Paid')->latest()->first();
                    if ($order) {
                        $combo = FoodAndDrink::find($data['comboId']);
                        OrderDetail::create([
                            'OrderId' => $order->OrderId,
                            'TicketId' => $ticketId,
                            'ItemId' => $data['comboId'],
                            'Quantity' => 1,
                            'Price' => $combo?->Price ?? 0,
                            'Status' => 'Active',
                        ]);
                    }
                }
            }

            DB::commit();
            $this->clearCache();

            return $ticket->load(['user', 'showtime.movie', 'seat', 'orderDetails.item']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteTicket($ticketId)
    {
        DB::beginTransaction();
        try {
            $ticket = Ticket::findOrFail($ticketId);

            $showtimeSeat = DB::table('showtimeseats')
                ->where('ShowtimeId', $ticket->ShowtimeId)
                ->where('SeatId', $ticket->SeatId)
                ->first();

            if ($showtimeSeat) {
                DB::table('showtimeseats')->where('Id', $showtimeSeat->Id)->update(['Status' => 'Available']);
            }

            OrderDetail::where('TicketId', $ticketId)->delete();
            $ticket->delete();

            DB::commit();
            $this->clearCache();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function clearCache()
    {
        Cache::forget('ticket_management_dashboard');
        Cache::forget('ticket_management_combos');
        Cache::forget('ticket_management_total_count');
        Cache::forget('ticket_management_stats');
        Cache::forget('ticket_management_daily');
    }
}
