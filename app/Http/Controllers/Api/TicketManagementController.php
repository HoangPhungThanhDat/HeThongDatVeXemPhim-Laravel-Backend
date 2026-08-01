<?php
// app/Http/Controllers/Api/TicketManagementController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Showtime;
use App\Models\Seat;
use App\Models\OrderDetail;
use App\Models\FoodAndDrink;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketManagementController extends Controller
{
    /**
     * 📊 API tổng hợp: Lấy tất cả dữ liệu cho trang quản lý vé
     */
    public function getDashboardData(Request $request)
    {
        try {
            $tickets = Ticket::with([
                'user' => function($q) {
                    $q->select('UserId', 'FullName', 'Email', 'PhoneNumber');
                },
                'showtime' => function($q) {
                    $q->select('ShowtimeId', 'StartTime', 'EndTime', 'Price', 'RoomId');
                },
                'showtime.room' => function($q) {
                    $q->select('RoomId', 'Name');
                },
                'showtime.movie' => function($q) {
                    $q->select('MovieId', 'Title', 'PosterUrl');
                },
                'seat' => function($q) {
                    $q->select('SeatId', 'Row', 'Number', 'SeatType');
                },
                'orderDetails' => function($q) {
                    $q->whereNotNull('ItemId')
                      ->with(['item' => function($q2) {
                          $q2->select('ItemId', 'Name', 'Price');
                      }]);
                }
            ])
            ->orderBy('CreatedAt', 'desc')
            ->get();

            $stats = [
                'total' => $tickets->count(),
                'paid' => $tickets->where('Status', 'Paid')->count(),
                'checkin' => $tickets->where('Status', 'CheckedIn')->count(),
                'pending' => $tickets->where('Status', 'Pending')->count(),
                'cancelled' => $tickets->where('Status', 'Cancelled')->count(),
                'expired' => $tickets->where('Status', 'Expired')->count(),
            ];

            $formattedTickets = $tickets->map(function($ticket) {
                $showtime = $ticket->showtime;
                $movie = $showtime?->movie;
                $room = $showtime?->room;
                $seat = $ticket->seat;
                $user = $ticket->user;

                $startTime = $showtime?->StartTime ? 
                    \Carbon\Carbon::parse($showtime->StartTime)->format('H:i d/m/Y') : '';

                $comboName = null;
                $comboPrice = 0;
                foreach ($ticket->orderDetails as $detail) {
                    if ($detail->item) {
                        $comboName = $detail->item->Name;
                        $comboPrice = $detail->item->Price ?? 0;
                        break;
                    }
                }

                return [
                    'TicketId' => $ticket->TicketId,
                    'Code' => 'VE' . str_pad($ticket->TicketId, 4, '0', STR_PAD_LEFT),
                    'UserId' => $user?->UserId,
                    'CustomerName' => $user?->FullName ?? 'Khách hàng',
                    'Email' => $user?->Email ?? '',
                    'Phone' => $user?->PhoneNumber ?? '',
                    'MovieTitle' => $movie?->Title ?? 'Phim',
                    'MoviePoster' => $movie?->PosterUrl ?? null,
                    'Showtime' => $startTime,
                    'ShowtimeId' => $showtime?->ShowtimeId,
                    'RoomName' => $room?->Name ?? '',
                    'SeatLabel' => $seat ? ($seat->Row . $seat->Number) : 'A1',
                    'SeatId' => $seat?->SeatId,
                    'SeatType' => $seat?->SeatType ?? 'Normal',
                    'Price' => $showtime?->Price ?? 0,
                    'Status' => $ticket->Status ?? 'Pending',
                    'Combo' => $comboName ?? 'Không có',
                    'ComboPrice' => $comboPrice,
                    'ComboId' => $ticket->orderDetails->first()?->ItemId,
                    'QRCode' => $ticket->QRCode ?? 'QR-' . $ticket->TicketId,
                    'Note' => $ticket->Note ?? '',
                    'BookingTime' => $ticket->BookingTime,
                    'CreatedAt' => $ticket->CreatedAt,
                    'UpdatedAt' => $ticket->UpdatedAt,
                ];
            });

            $combos = FoodAndDrink::where('IsAvailable', true)
                ->orWhere('Status', 'Active')
                ->get(['ItemId', 'Name', 'Price', 'Description', 'ImageUrl']);

            return response()->json([
                'success' => true,
                'message' => 'Lấy dữ liệu vé thành công',
                'data' => [
                    'tickets' => $formattedTickets,
                    'stats' => $stats,
                    'combos' => $combos,
                ],
                'meta' => [
                    'total_tickets' => $tickets->count(),
                    'fetched_at' => now()->toDateTimeString()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('TicketManagementController::getDashboardData', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể tải dữ liệu vé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🎯 API lấy chi tiết vé
     */
    public function getTicketDetail($ticketId)
    {
        try {
            $ticket = Ticket::with([
                'user',
                'showtime.movie',
                'showtime.room',
                'seat',
                'orderDetails.item',
                'orderDetails.order'
            ])->findOrFail($ticketId);

            $showtime = $ticket->showtime;
            $movie = $showtime?->movie;
            $room = $showtime?->room;
            $seat = $ticket->seat;
            $user = $ticket->user;

            $combos = [];
            foreach ($ticket->orderDetails as $detail) {
                if ($detail->item) {
                    $combos[] = [
                        'OrderDetailId' => $detail->OrderDetailId,
                        'ItemId' => $detail->ItemId,
                        'Name' => $detail->item->Name,
                        'Price' => $detail->item->Price,
                        'Quantity' => $detail->Quantity,
                        'ImageUrl' => $detail->item->ImageUrl,
                    ];
                }
            }

            $data = [
                'TicketId' => $ticket->TicketId,
                'Code' => 'VE' . str_pad($ticket->TicketId, 4, '0', STR_PAD_LEFT),
                'User' => [
                    'UserId' => $user?->UserId,
                    'FullName' => $user?->FullName ?? 'Khách hàng',
                    'Email' => $user?->Email ?? '',
                    'Phone' => $user?->PhoneNumber ?? '',
                ],
                'Movie' => [
                    'MovieId' => $movie?->MovieId,
                    'Title' => $movie?->Title ?? 'Phim',
                    'PosterUrl' => $movie?->PosterUrl ?? null,
                    'Duration' => $movie?->Duration ?? 0,
                ],
                'Showtime' => [
                    'ShowtimeId' => $showtime?->ShowtimeId,
                    'StartTime' => $showtime?->StartTime,
                    'EndTime' => $showtime?->EndTime,
                    'Price' => $showtime?->Price ?? 0,
                ],
                'Room' => [
                    'RoomId' => $room?->RoomId,
                    'Name' => $room?->Name ?? '',
                ],
                'Seat' => [
                    'SeatId' => $seat?->SeatId,
                    'Row' => $seat?->Row ?? '',
                    'Number' => $seat?->Number ?? '',
                    'Label' => $seat ? ($seat->Row . $seat->Number) : 'A1',
                    'SeatType' => $seat?->SeatType ?? 'Normal',
                ],
                'Status' => $ticket->Status ?? 'Pending',
                'QRCode' => $ticket->QRCode ?? 'QR-' . $ticket->TicketId,
                'Note' => $ticket->Note ?? '',
                'BookingTime' => $ticket->BookingTime,
                'CreatedAt' => $ticket->CreatedAt,
                'UpdatedAt' => $ticket->UpdatedAt,
                'Combos' => $combos,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Lấy chi tiết vé thành công',
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('TicketManagementController::getTicketDetail', [
                'ticketId' => $ticketId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể tải chi tiết vé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📊 API lấy thống kê vé
     */
    public function getStatistics(Request $request)
    {
        try {
            $total = Ticket::count();
            $paid = Ticket::where('Status', 'Paid')->count();
            $checkin = Ticket::where('Status', 'CheckedIn')->count();
            $pending = Ticket::where('Status', 'Pending')->count();
            $cancelled = Ticket::where('Status', 'Cancelled')->count();
            $expired = Ticket::where('Status', 'Expired')->count();

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

            $dailyStats = Ticket::select(
                DB::raw('DATE(CreatedAt) as date'),
                DB::raw('COUNT(*) as total'),
                DB::raw('SUM(CASE WHEN Status = "Paid" THEN 1 ELSE 0 END) as paid'),
                DB::raw('SUM(CASE WHEN Status = "Cancelled" THEN 1 ELSE 0 END) as cancelled')
            )
            ->where('CreatedAt', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'paid' => $paid,
                    'checkin' => $checkin,
                    'pending' => $pending,
                    'cancelled' => $cancelled,
                    'expired' => $expired,
                    'by_status' => $statusData,
                    'daily' => $dailyStats
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('TicketManagementController::getStatistics', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể tải thống kê vé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🎫 Tạo vé mới (có thể tạo nhiều vé cùng lúc)
     */
    public function storeTickets(Request $request)
    {
        try {
            $request->validate([
                'userId' => 'required|exists:users,UserId',
                'showtimeId' => 'required|exists:showtimes,ShowtimeId',
                'seats' => 'required|array|min:1',
                'seats.*.seatId' => 'required|exists:seats,SeatId',
                'seats.*.showtimeSeatId' => 'required|exists:showtimeseats,Id',
                'status' => 'nullable|in:Paid,CheckedIn,Pending,Cancelled,Expired',
                'note' => 'nullable|string|max:500',
                'comboId' => 'nullable|exists:foodanddrinks,ItemId',
            ]);

            DB::beginTransaction();

            $userId = $request->userId;
            $showtimeId = $request->showtimeId;
            $status = $request->status ?? 'Paid';
            $note = $request->note ?? '';
            $comboId = $request->comboId;
            $seats = $request->seats;

            $showtime = Showtime::with(['movie', 'room'])->find($showtimeId);
            if (!$showtime) {
                throw new \Exception('Không tìm thấy suất chiếu');
            }

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

                if ($existingTicket) {
                    throw new \Exception("Ghế đã được đặt, vui lòng chọn ghế khác");
                }

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
                    DB::table('showtimeseats')
                        ->where('Id', $seatData['showtimeSeatId'])
                        ->update(['Status' => 'Reserved']);
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

            $ticketIds = array_column($createdTickets, 'TicketId');
            $tickets = Ticket::with(['user', 'showtime.movie', 'seat'])
                ->whereIn('TicketId', $ticketIds)
                ->get();

            return response()->json([
                'success' => true,
                'message' => "Đã tạo " . count($createdTickets) . " vé thành công",
                'data' => [
                    'order_id' => $order->OrderId,
                    'tickets' => $tickets,
                    'total_amount' => $totalAmount,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TicketManagementController::storeTickets', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage() ?: 'Không thể tạo vé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🎫 Cập nhật vé
     */
    public function updateTicket(Request $request, $ticketId)
    {
        try {
            $request->validate([
                'userId' => 'nullable|exists:users,UserId',
                'showtimeId' => 'nullable|exists:showtimes,ShowtimeId',
                'seatId' => 'nullable|exists:seats,SeatId',
                'status' => 'nullable|in:Paid,CheckedIn,Pending,Cancelled,Expired',
                'note' => 'nullable|string|max:500',
                'oldShowtimeSeatId' => 'nullable|exists:showtimeseats,Id',
                'newShowtimeSeatId' => 'nullable|exists:showtimeseats,Id',
                'comboId' => 'nullable|exists:foodanddrinks,ItemId',
            ]);

            DB::beginTransaction();

            $ticket = Ticket::findOrFail($ticketId);
            $oldSeatId = $ticket->SeatId;

            $ticket->update([
                'UserId' => $request->userId ?? $ticket->UserId,
                'ShowtimeId' => $request->showtimeId ?? $ticket->ShowtimeId,
                'SeatId' => $request->seatId ?? $ticket->SeatId,
                'Status' => $request->status ?? $ticket->Status,
                'Note' => $request->note ?? $ticket->Note,
            ]);

            if ($request->seatId && $request->seatId != $oldSeatId) {
                if ($request->oldShowtimeSeatId) {
                    DB::table('showtimeseats')
                        ->where('Id', $request->oldShowtimeSeatId)
                        ->update(['Status' => 'Available']);
                }

                if ($request->newShowtimeSeatId) {
                    DB::table('showtimeseats')
                        ->where('Id', $request->newShowtimeSeatId)
                        ->update(['Status' => 'Reserved']);
                }
            }

            if ($request->has('comboId')) {
                $orderDetail = OrderDetail::where('TicketId', $ticketId)
                    ->whereNotNull('ItemId')
                    ->first();

                if ($orderDetail) {
                    $orderDetail->update(['ItemId' => $request->comboId]);
                } else if ($request->comboId) {
                    $order = Order::where('UserId', $ticket->UserId)
                        ->where('Status', 'Paid')
                        ->latest()
                        ->first();

                    if ($order) {
                        $combo = FoodAndDrink::find($request->comboId);
                        OrderDetail::create([
                            'OrderId' => $order->OrderId,
                            'TicketId' => $ticketId,
                            'ItemId' => $request->comboId,
                            'Quantity' => 1,
                            'Price' => $combo?->Price ?? 0,
                            'Status' => 'Active',
                        ]);
                    }
                }
            }

            DB::commit();

            $ticket->load(['user', 'showtime.movie', 'seat', 'orderDetails.item']);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật vé thành công',
                'data' => $ticket
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TicketManagementController::updateTicket', [
                'ticketId' => $ticketId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể cập nhật vé',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🗑️ Xóa vé
     */
    public function deleteTicket($ticketId)
    {
        try {
            DB::beginTransaction();

            $ticket = Ticket::findOrFail($ticketId);

            $showtimeSeat = DB::table('showtimeseats')
                ->where('ShowtimeId', $ticket->ShowtimeId)
                ->where('SeatId', $ticket->SeatId)
                ->first();

            if ($showtimeSeat) {
                DB::table('showtimeseats')
                    ->where('Id', $showtimeSeat->Id)
                    ->update(['Status' => 'Available']);
            }

            OrderDetail::where('TicketId', $ticketId)->delete();
            $ticket->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa vé thành công'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('TicketManagementController::deleteTicket', [
                'ticketId' => $ticketId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa vé',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}