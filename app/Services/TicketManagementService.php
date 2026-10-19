<?php

namespace App\Services;

use App\Repositories\TicketManagementRepository;
use Illuminate\Support\Facades\Log;

class TicketManagementService
{
    protected $repository;

    public function __construct(TicketManagementRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getDashboardData()
    {
        try {
            $tickets = $this->repository->getDashboardTickets();
            $combos = $this->repository->getAvailableCombos();

            $stats = [
                'total' => $tickets->count(),
                'paid' => $tickets->where('Status', 'Paid')->count(),
                'checkin' => $tickets->where('Status', 'CheckedIn')->count(),
                'pending' => $tickets->where('Status', 'Pending')->count(),
                'cancelled' => $tickets->where('Status', 'Cancelled')->count(),
                'expired' => $tickets->where('Status', 'Expired')->count(),
            ];

            $formattedTickets = $tickets->map(function($ticket) {
                return $this->formatTicketData($ticket);
            });

            return [
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
            ];
        } catch (\Exception $e) {
            Log::error('TicketManagementService::getDashboardData', ['error' => $e->getMessage()]);
            return ['error' => true, 'success' => false, 'message' => 'Không thể tải dữ liệu vé', 'error' => $e->getMessage()];
        }
    }

    public function getTicketDetail($ticketId)
    {
        try {
            $ticket = $this->repository->getTicketDetail($ticketId);
            if (!$ticket) {
                return ['error' => true, 'success' => false, 'message' => 'Không tìm thấy vé'];
            }

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

            return [
                'success' => true,
                'message' => 'Lấy chi tiết vé thành công',
                'data' => $data
            ];
        } catch (\Exception $e) {
            Log::error('TicketManagementService::getTicketDetail', ['ticketId' => $ticketId, 'error' => $e->getMessage()]);
            return ['error' => true, 'success' => false, 'message' => 'Không thể tải chi tiết vé', 'error' => $e->getMessage()];
        }
    }

    public function getStatistics()
    {
        try {
            $total = $this->repository->countTickets();
            $statusData = $this->repository->getTicketStatsByStatus();
            $dailyStats = $this->repository->getDailyTicketStats();

            return [
                'success' => true,
                'data' => [
                    'total' => $total,
                    'by_status' => $statusData,
                    'daily' => $dailyStats
                ]
            ];
        } catch (\Exception $e) {
            Log::error('TicketManagementService::getStatistics', ['error' => $e->getMessage()]);
            return ['error' => true, 'success' => false, 'message' => 'Không thể tải thống kê vé', 'error' => $e->getMessage()];
        }
    }

    public function storeTickets(array $data)
    {
        try {
            $result = $this->repository->storeTickets($data);
            return [
                'success' => true,
                'message' => "Đã tạo " . count($result['tickets']) . " vé thành công",
                'data' => $result
            ];
        } catch (\Exception $e) {
            Log::error('TicketManagementService::storeTickets', ['error' => $e->getMessage()]);
            return ['error' => true, 'success' => false, 'message' => $e->getMessage() ?: 'Không thể tạo vé', 'error' => $e->getMessage()];
        }
    }

    public function updateTicket($ticketId, array $data)
    {
        try {
            $ticket = $this->repository->updateTicket($ticketId, $data);
            return [
                'success' => true,
                'message' => 'Cập nhật vé thành công',
                'data' => $ticket
            ];
        } catch (\Exception $e) {
            Log::error('TicketManagementService::updateTicket', ['ticketId' => $ticketId, 'error' => $e->getMessage()]);
            return ['error' => true, 'success' => false, 'message' => 'Không thể cập nhật vé', 'error' => $e->getMessage()];
        }
    }

    public function deleteTicket($ticketId)
    {
        try {
            $this->repository->deleteTicket($ticketId);
            return [
                'success' => true,
                'message' => 'Xóa vé thành công'
            ];
        } catch (\Exception $e) {
            Log::error('TicketManagementService::deleteTicket', ['ticketId' => $ticketId, 'error' => $e->getMessage()]);
            return ['error' => true, 'success' => false, 'message' => 'Không thể xóa vé', 'error' => $e->getMessage()];
        }
    }

    private function formatTicketData($ticket)
    {
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
    }
}