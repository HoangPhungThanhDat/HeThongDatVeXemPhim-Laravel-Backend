<?php

namespace App\Services;

use App\Repositories\SeatManagementRepository;
use Illuminate\Support\Facades\Log;

class SeatManagementService
{
    protected $repository;

    public function __construct(SeatManagementRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getData($request)
    {
        try {
            $cinemas = $this->repository->getCinemas();
            $rooms = $this->repository->getRooms($request->cinema_id);
            $showtimes = $this->repository->getShowtimes($request->cinema_id, $request->room_id);

            $seats = [];
            $roomInfo = null;
            $stats = [
                'total' => 0, 'trong' => 0, 'dat' => 0, 'giu' => 0, 'loi' => 0, 'baoTri' => 0, 'room_total' => 0,
            ];

            if ($request->showtime_id) {
                $showtime = $this->repository->getShowtimeById($request->showtime_id);

                if ($showtime && $showtime->room) {
                    $roomInfo = [
                        'id' => $showtime->room->RoomId,
                        'name' => $showtime->room->Name,
                        'room_type' => $showtime->room->RoomType,
                        'seat_count' => $showtime->room->SeatCount ?? 0,
                        'status' => $showtime->room->Status,
                    ];
                    $stats['room_total'] = $showtime->room->SeatCount ?? 0;

                    $showtimeSeats = $this->repository->getShowtimeSeats($request->showtime_id);

                    if ($showtimeSeats->isEmpty()) {
                        $this->repository->generateSeatsForRoom($showtime->room->RoomId, $request->showtime_id);
                        $showtimeSeats = $this->repository->getShowtimeSeats($request->showtime_id);
                    }

                    $seatIds = $showtimeSeats->pluck('SeatId')->unique()->toArray();
                    $tickets = $this->repository->getTicketsBySeats($request->showtime_id, $seatIds);

                    $seats = $showtimeSeats->map(function ($ss) use ($tickets) {
                        return $this->mapSeatData($ss, $tickets->get($ss->SeatId));
                    });
                }
            }

            $stats['total'] = count($seats);
            foreach ($seats as $seat) {
                if (isset($stats[$seat['status']])) {
                    $stats[$seat['status']]++;
                }
            }

            return [
                'success' => true,
                'data' => [
                    'cinemas' => $cinemas,
                    'rooms' => $rooms,
                    'showtimes' => $showtimes,
                    'seats' => $seats,
                    'stats' => $stats,
                    'room' => $roomInfo,
                    'filters' => [
                        'cinema_id' => $request->cinema_id,
                        'room_id' => $request->room_id,
                        'showtime_id' => $request->showtime_id,
                    ],
                ],
            ];
        } catch (\Exception $e) {
            Log::error('SeatManagementService@getData: ' . $e->getMessage());
            return ['error' => true, 'success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()];
        }
    }

    public function getSeatsByShowtime($showtimeId, $request)
    {
        try {
            $showtime = $this->repository->getShowtimeById($showtimeId);
            
            if ($showtime && $showtime->room) {
                $existingSeats = $this->repository->countShowtimeSeats($showtimeId);
                if ($existingSeats == 0) {
                    $this->repository->generateSeatsForRoom($showtime->room->RoomId, $showtimeId);
                }
            }

            $perPage = $request->per_page ?? 100;
            $page = $request->page ?? 1;
            $showtimeSeats = $this->repository->getPaginatedShowtimeSeats($showtimeId, $perPage, $page);

            $seatIds = $showtimeSeats->pluck('SeatId')->unique()->toArray();
            $tickets = $this->repository->getTicketsBySeats($showtimeId, $seatIds);

            $seats = $showtimeSeats->map(function ($ss) use ($tickets) {
                return $this->mapSeatData($ss, $tickets->get($ss->SeatId));
            });

            $stats = $this->repository->getSeatStats($showtimeId);
            $roomTotal = $showtime && $showtime->room ? ($showtime->room->SeatCount ?? 0) : 0;

            return [
                'success' => true,
                'data' => [
                    'seats' => $seats,
                    'stats' => $stats,
                    'total' => $showtimeSeats->total(),
                    'room_total' => $roomTotal,
                    'current_page' => $showtimeSeats->currentPage(),
                    'per_page' => $showtimeSeats->perPage(),
                    'last_page' => $showtimeSeats->lastPage(),
                ],
            ];
        } catch (\Exception $e) {
            Log::error('SeatManagementService@getSeatsByShowtime: ' . $e->getMessage());
            return ['error' => true, 'success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()];
        }
    }

    public function generateSeats($showtimeId)
    {
        try {
            $showtime = $this->repository->getShowtimeById($showtimeId);
            if (!$showtime) {
                return ['error' => true, 'success' => false, 'message' => 'Không tìm thấy suất chiếu', 'code' => 404];
            }

            $data = $this->repository->generateSeats($showtimeId);

            return [
                'success' => true,
                'message' => 'Đã tạo ' . $data['total_seats'] . ' ghế cho suất chiếu',
                'data' => $data,
            ];
        } catch (\Exception $e) {
            Log::error('SeatManagementService@generateSeats: ' . $e->getMessage());
            return ['error' => true, 'success' => false, 'message' => 'Lỗi tạo ghế: ' . $e->getMessage()];
        }
    }

    public function updateSeat($seatId, $status)
    {
        try {
            $result = $this->repository->updateSeat($seatId, $status);
            if (!$result) {
                return ['error' => true, 'success' => false, 'message' => 'Không tìm thấy ghế', 'code' => 404];
            }

            return [
                'success' => true,
                'message' => 'Cập nhật trạng thái thành công',
                'data' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('SeatManagementService@updateSeat: ' . $e->getMessage());
            return ['error' => true, 'success' => false, 'message' => 'Lỗi cập nhật: ' . $e->getMessage()];
        }
    }

    public function resetSeat($seatId)
    {
        try {
            $result = $this->repository->resetSeat($seatId);
            if (!$result) {
                return ['error' => true, 'success' => false, 'message' => 'Không tìm thấy ghế', 'code' => 404];
            }

            return [
                'success' => true,
                'message' => 'Đã reset ghế về trạng thái trống',
                'data' => $result,
            ];
        } catch (\Exception $e) {
            Log::error('SeatManagementService@resetSeat: ' . $e->getMessage());
            return ['error' => true, 'success' => false, 'message' => 'Lỗi reset: ' . $e->getMessage()];
        }
    }

    public function deleteSeat($seatId)
    {
        try {
            $deleted = $this->repository->deleteSeat($seatId);
            if (!$deleted) {
                return ['error' => true, 'success' => false, 'message' => 'Không tìm thấy ghế để xóa', 'code' => 404];
            }

            return [
                'success' => true,
                'message' => 'Đã xóa ghế thành công',
                'data' => ['id' => $seatId, 'deleted' => true],
            ];
        } catch (\Exception $e) {
            Log::error('SeatManagementService@deleteSeat: ' . $e->getMessage());
            return ['error' => true, 'success' => false, 'message' => 'Lỗi xóa: ' . $e->getMessage()];
        }
    }

    public function deleteAllSeatsByShowtime($showtimeId)
    {
        try {
            $deleted = $this->repository->deleteAllSeatsByShowtime($showtimeId);

            return [
                'success' => true,
                'message' => "Đã xóa {$deleted} ghế của suất chiếu",
                'data' => ['showtime_id' => $showtimeId, 'deleted_count' => $deleted],
            ];
        } catch (\Exception $e) {
            Log::error('SeatManagementService@deleteAllSeatsByShowtime: ' . $e->getMessage());
            return ['error' => true, 'success' => false, 'message' => 'Lỗi xóa: ' . $e->getMessage()];
        }
    }

    private function mapSeatData($ss, $ticket)
    {
        $user = $ticket ? $ticket->user : null;
        $seatType = $ss->seat->SeatType ?? 'Normal';
        $statusLocal = $this->mapStatusToLocal($ss->Status);

        return [
            'id' => $ss->ShowtimeSeatId,
            'showtime_id' => $ss->ShowtimeId,
            'seat_id' => $ss->SeatId,
            'row' => $ss->seat->Row ?? 'A',
            'number' => $ss->seat->Number ?? 0,
            'seat_type' => strtolower($seatType),
            'seat_type_raw' => $seatType,
            'status' => $statusLocal,
            'status_raw' => $ss->Status,
            'ticket_code' => $ticket ? ($ticket->TicketCode ?? null) : null,
            'customer_name' => $user ? ($user->FullName ?? null) : null,
            'created_at' => $ss->CreatedAt,
            'updated_at' => $ss->UpdatedAt,
        ];
    }

    private function mapStatusToLocal($status)
    {
        $map = [
            'Available' => 'trong', 'Reserved' => 'dat', 'Held' => 'giu',
            'Broken' => 'loi', 'Inactive' => 'baoTri',
        ];
        return $map[$status] ?? 'trong';
    }
}