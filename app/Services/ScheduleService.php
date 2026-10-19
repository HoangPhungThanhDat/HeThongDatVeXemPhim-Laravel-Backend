<?php

namespace App\Services;

use App\Repositories\ScheduleRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleService
{
    protected $scheduleRepository;

    public function __construct(ScheduleRepository $scheduleRepository)
    {
        $this->scheduleRepository = $scheduleRepository;
    }

    public function getAll()
    {
        return $this->scheduleRepository->getAll();
    }

    public function getById($id)
    {
        return $this->scheduleRepository->getById($id);
    }

    public function create(array $data): array
    {
        $daysArray = $data['DaysOfWeek'] ?? [];
        if (is_array($daysArray)) {
            $data['DaysOfWeek'] = implode(',', $daysArray);
        }

        $data['CreatedAt'] = now();
        $data['CreatedBy'] = Auth::user()?->UserId;

        DB::beginTransaction();
        try {
            $schedule = $this->scheduleRepository->create($data);

            [$showtimesCreated, $seatsCreated] = $this->generateShowtimesAndSeats(
                $schedule,
                $daysArray,
                $data
            );

            DB::commit();

            return [
                'schedule'          => $schedule,
                'showtimes_created' => $showtimesCreated,
                'seats_created'     => $seatsCreated,
            ];
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('[ScheduleService] Lỗi khi tạo Schedule + Showtimes: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        if (isset($data['DaysOfWeek']) && is_array($data['DaysOfWeek'])) {
            $data['DaysOfWeek'] = implode(',', $data['DaysOfWeek']);
        }
        $data['UpdatedBy'] = Auth::user()?->UserId;
        $data['UpdatedAt'] = now();
        return $this->scheduleRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->scheduleRepository->delete($id);
    }

    private function generateShowtimesAndSeats($schedule, array $daysArray, array $data): array
    {
        if (empty($daysArray)) {
            return [0, 0];
        }

        $startDate  = Carbon::parse($data['StartDate'])->startOfDay();
        $endDate    = Carbon::parse($data['EndDate'])->startOfDay();
        $startTime  = $data['StartTime'];
        $endTime    = $data['EndTime'];
        $movieId    = $data['MovieId'];
        $roomId     = $data['RoomId'];
        $price      = $data['Price'];
        $createdBy  = Auth::user()?->UserId;

        $showtimesCreated = 0;
        $seatsCreated     = 0;
        $current          = $startDate->copy();
        $showtimesData    = [];

        // 1. Chuẩn bị dữ liệu Showtime
        while ($current->lte($endDate)) {
            $dayAbbr = $current->format('D');

            if (in_array($dayAbbr, $daysArray, true)) {
                $showtimeStart = Carbon::parse($current->toDateString() . ' ' . $startTime);
                $showtimeEnd   = Carbon::parse($current->toDateString() . ' ' . $endTime);

                $showtimesData[] = [
                    'MovieId'   => $movieId,
                    'RoomId'    => $roomId,
                    'StartTime' => $showtimeStart,
                    'EndTime'   => $showtimeEnd,
                    'Price'     => $price,
                    'Status'    => 'Scheduled',
                    'CreatedAt' => now(),
                    'CreatedBy' => $createdBy,
                ];
            }
            $current->addDay();
        }

        // 2. Gọi Repository để lưu Showtimes
        if (!empty($showtimesData)) {
            $createdShowtimes = $this->scheduleRepository->insertShowtimes($showtimesData);
            $showtimesCreated = count($createdShowtimes);

            // 3. Chuẩn bị dữ liệu ShowtimeSeats (Dựa vào ghế của phòng)
            $seatIds = $this->scheduleRepository->getActiveSeatIdsByRoom($roomId);
            
            if (!empty($seatIds)) {
                $seatsData = [];
                $now = now();
                foreach ($createdShowtimes as $showtime) {
                    foreach ($seatIds as $seatId) {
                        $seatsData[] = [
                            'ShowtimeId' => $showtime->ShowtimeId,
                            'SeatId'     => $seatId,
                            'Status'     => 'Available',
                            'CreatedAt'  => $now,
                            'UpdatedAt'  => $now,
                            'CreatedBy'  => $createdBy,
                        ];
                    }
                }

                // 4. Gọi Repository để bulk insert ghế
                if (!empty($seatsData)) {
                    $this->scheduleRepository->insertShowtimeSeats($seatsData);
                    $seatsCreated = count($seatsData);
                }
            }
        }

        return [$showtimesCreated, $seatsCreated];
    }
}