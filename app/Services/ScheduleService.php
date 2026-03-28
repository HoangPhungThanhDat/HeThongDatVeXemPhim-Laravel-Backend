<?php

// namespace App\Services;

// use App\Repositories\ScheduleRepository;
// use Illuminate\Support\Facades\Auth;


// class ScheduleService
// {
//     protected $scheduleRepository;

//     public function __construct(ScheduleRepository $scheduleRepository)
//     {
//         $this->scheduleRepository = $scheduleRepository;
//     }

//     public function getAll()
//     {
//         return $this->scheduleRepository->getAll();
//     }

//     public function getById($id)
//     {
//         return $this->scheduleRepository->getById($id);
//     }

//     public function create(array $data)
//     {
//         $data['CreatedBy'] = Auth::user()->UserId;
//         return $this->scheduleRepository->create($data);
//     }

//     public function update($id, array $data)
//     {
//         $data['UpdatedBy'] = Auth::user()->UserId;
//         return $this->scheduleRepository->update($id, $data);
//     }

//     public function delete($id)
//     {
//         return $this->scheduleRepository->delete($id);
//     }
// }











namespace App\Services;

use App\Repositories\ScheduleRepository;
use App\Models\Showtime;
use App\Models\Seat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScheduleService
{
    protected $scheduleRepository;
    protected $showtimeseatService;

    // Map tên ngày (frontend gửi) → Carbon dayOfWeek constant
    private const DAY_MAP = [
        'Mon' => Carbon::MONDAY,
        'Tue' => Carbon::TUESDAY,
        'Wed' => Carbon::WEDNESDAY,
        'Thu' => Carbon::THURSDAY,
        'Fri' => Carbon::FRIDAY,
        'Sat' => Carbon::SATURDAY,
        'Sun' => Carbon::SUNDAY,
    ];

    public function __construct(
        ScheduleRepository $scheduleRepository,
        ShowtimeseatService $showtimeseatService   // ← inject thêm
    ) {
        $this->scheduleRepository  = $scheduleRepository;
        $this->showtimeseatService = $showtimeseatService;
    }

    public function getAll()
    {
        return $this->scheduleRepository->getAll();
    }

    public function getById($id)
    {
        return $this->scheduleRepository->getById($id);
    }

    /**
     * Tạo Schedule → sinh Showtime theo DaysOfWeek → generate ShowtimeSeats cho mỗi Showtime
     *
     * @param  array $data  validated data từ StoreScheduleRequest
     * @return array{schedule: Schedule, showtimes_created: int, seats_created: int}
     */
    public function create(array $data): array
    {
        // DaysOfWeek từ frontend là array ['Mon','Tue',...] 
        // → lưu DB dạng string "Mon,Tue,..." (SET column)
        $daysArray = $data['DaysOfWeek'] ?? [];
        if (is_array($daysArray)) {
            $data['DaysOfWeek'] = implode(',', $daysArray);
        }

        $data['CreatedAt'] = now();
        $data['CreatedBy'] = Auth::user()?->UserId;

        DB::beginTransaction();
        try {
            // 1. Tạo Schedule
            $schedule = $this->scheduleRepository->create($data);

            // 2. Sinh Showtimes + ShowtimeSeats trong transaction
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
        // DaysOfWeek cũng có thể được gửi dạng array khi update
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

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Duyệt từng ngày trong khoảng StartDate → EndDate,
     * nếu ngày đó nằm trong DaysOfWeek thì tạo Showtime rồi generate ghế.
     *
     * @return array{0: int, 1: int}  [số showtime đã tạo, tổng số ghế đã tạo]
     */
    private function generateShowtimesAndSeats(
        $schedule,
        array $daysArray,
        array $data
    ): array {
        if (empty($daysArray)) {
            return [0, 0];
        }

        $startDate  = Carbon::parse($data['StartDate'])->startOfDay();
        $endDate    = Carbon::parse($data['EndDate'])->startOfDay();
        $startTime  = $data['StartTime']; // "HH:MM" hoặc "HH:MM:SS"
        $endTime    = $data['EndTime'];
        $movieId    = $data['MovieId'];
        $roomId     = $data['RoomId'];
        $price      = $data['Price'];
        $createdBy  = Auth::user()?->UserId;

        $showtimesCreated = 0;
        $seatsCreated     = 0;
        $current          = $startDate->copy();

        while ($current->lte($endDate)) {
            // Carbon::format('D') → 'Mon','Tue',... (locale-independent)
            $dayAbbr = $current->format('D');

            if (in_array($dayAbbr, $daysArray, true)) {
                // Ghép ngày hiện tại + giờ từ schedule
                $showtimeStart = Carbon::parse($current->toDateString() . ' ' . $startTime);
                $showtimeEnd   = Carbon::parse($current->toDateString() . ' ' . $endTime);

                // 2a. Tạo Showtime
                $showtime = Showtime::create([
                    'MovieId'   => $movieId,
                    'RoomId'    => $roomId,
                    'StartTime' => $showtimeStart,
                    'EndTime'   => $showtimeEnd,
                    'Price'     => $price,
                    'Status'    => 'Scheduled',
                    'CreatedAt' => now(),
                    'CreatedBy' => $createdBy,
                ]);

                $showtimesCreated++;

                // 2b. Generate ShowtimeSeats dựa vào tất cả ghế của phòng
                $seatsCount = $this->generateShowtimeSeatsForRoom(
                    $showtime->ShowtimeId,
                    $roomId,
                    $createdBy
                );
                $seatsCreated += $seatsCount;
            }

            $current->addDay();
        }

        return [$showtimesCreated, $seatsCreated];
    }

    /**
     * Lấy toàn bộ ghế của phòng → bulk insert vào showtimeseats.
     * Chỉ lấy ghế có Status != 'Inactive' (ghế đang hoạt động).
     *
     * @return int  số ghế đã tạo
     */
    private function generateShowtimeSeatsForRoom(
        int $showtimeId,
        int $roomId,
        ?int $createdBy
    ): int {
        $seats = Seat::where('RoomId', $roomId)
                     ->where('Status', '!=', 'Inactive')
                     ->get(['SeatId']);

        if ($seats->isEmpty()) {
            return 0;
        }

        $now  = now();
        $rows = $seats->map(fn ($seat) => [
            'ShowtimeId' => $showtimeId,
            'SeatId'     => $seat->SeatId,
            'Status'     => 'Available',
            'CreatedAt'  => $now,
            'UpdatedAt'  => $now,
            'CreatedBy'  => $createdBy,
        ])->toArray();

        // Bulk insert — nhanh, không kích hoạt Eloquent events
        \App\Models\Showtimeseat::insert($rows);

        return count($rows);
    }
}