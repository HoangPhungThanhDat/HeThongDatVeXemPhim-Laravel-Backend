<?php

namespace App\Repositories;

use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Showtime;
use App\Models\Schedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class CinemaRepository
{
    const CACHE_TIME = 1800; // 30 phút (Rạp ít thay đổi nhưng có nhiều phòng)

    /**
     * Lấy tất cả rạp (kèm rooms + seats) - ✅ ĐÃ CACHE
     */
    public function getAllWithRelations()
    {
        return Cache::remember('cinemas_with_relations', self::CACHE_TIME, function () {
            return Cinema::with(['rooms', 'rooms.seats'])->get();
        });
    }

    public function getAll()
    {
        return Cache::remember('cinemas_all', self::CACHE_TIME, function () {
            return Cinema::all();
        });
    }

    public function getById($id)
    {
        return Cache::remember("cinema_detail_{$id}", self::CACHE_TIME, function () use ($id) {
            return Cinema::findOrFail($id);
        });
    }

    public function getByIdWithRelations($id)
    {
        return Cache::remember("cinema_detail_rel_{$id}", self::CACHE_TIME, function () use ($id) {
            return Cinema::with(['rooms', 'rooms.seats'])->find($id);
        });
    }

    public function create(array $data)
    {
        $cinema = Cinema::create($data);
        $this->clearCache();
        return $cinema;
    }

    public function update($id, array $data)
    {
        $cinema = Cinema::findOrFail($id);
        $cinema->update($data);
        $this->clearCache($id);
        return $cinema;
    }

    public function delete($id)
    {
        $cinema = Cinema::findOrFail($id);
        $result = $cinema->delete();
        $this->clearCache($id);
        return $result;
    }

    public function getStats($cinemaId = null)
    {
        if ($cinemaId) {
            $cinema = $this->getByIdWithRelations($cinemaId);
            if (!$cinema) return null;
            
            $totalRooms = $cinema->rooms->count();
            $totalSeats = 0;
            $activeRooms = 0;
            foreach ($cinema->rooms as $room) {
                $totalSeats += $room->seats->count();
                if ($room->Status === 'Active') $activeRooms++;
            }
            
            return [
                'success' => true,
                'data' => [
                    'CinemaId' => $cinema->CinemaId,
                    'Name' => $cinema->Name,
                    'TotalRooms' => $totalRooms,
                    'ActiveRooms' => $activeRooms,
                    'TotalSeats' => $totalSeats,
                ]
            ];
        }

        $cinemas = $this->getAllWithRelations();
        $stats = [];
        foreach ($cinemas as $cinema) {
            $totalRooms = $cinema->rooms->count();
            $totalSeats = 0;
            $activeRooms = 0;
            foreach ($cinema->rooms as $room) {
                $totalSeats += $room->seats->count();
                if ($room->Status === 'Active') $activeRooms++;
            }
            $stats[] = [
                'CinemaId' => $cinema->CinemaId,
                'Name' => $cinema->Name,
                'TotalRooms' => $totalRooms,
                'ActiveRooms' => $activeRooms,
                'TotalSeats' => $totalSeats,
            ];
        }

        return ['success' => true, 'data' => $stats];
    }

    public function getShowtimesByCinema($cinemaId, $date)
    {
        $this->generateShowtimesFromSchedules($cinemaId, $date);

        return Showtime::with(['movie', 'movie.directors', 'movie.actors', 'room'])
            ->whereHas('room', fn($q) => $q->where('CinemaId', $cinemaId))
            ->whereDate('StartTime', $date)
            ->orderBy('StartTime')
            ->get();
    }

    public function getShowtimesByMovie($movieId)
    {
        $movie = Movie::where('MovieId', $movieId)->orWhere('Slug', $movieId)->first();
        if (!$movie) return collect();

        return Showtime::with(['room.cinema', 'movie.directors', 'movie.actors'])
            ->where('MovieId', $movie->MovieId)
            ->orderBy('StartTime')
            ->get()
            ->filter(fn($s) => $s->room && $s->room->cinema);
    }

    public function getMoviesByCinema($cinemaId)
    {
        $cinema = Cinema::find($cinemaId);
        if (!$cinema) return ['success' => false, 'message' => 'Không tìm thấy rạp'];

        $movieIds = Showtime::whereHas('room', fn($q) => $q->where('CinemaId', $cinemaId))
            ->where('StartTime', '>=', now())
            ->where('Status', 'Scheduled')
            ->pluck('MovieId')
            ->unique();

        $movies = Movie::whereIn('MovieId', $movieIds)
            ->where('Status', 'NowShowing')
            ->with(['genres', 'directors', 'actors'])
            ->get()
            ->map(fn($movie) => [
                'MovieId' => $movie->MovieId,
                'Title' => $movie->Title,
                'Slug' => $movie->Slug,
                'PosterUrl' => $movie->PosterUrl,
                'Duration' => $movie->Duration,
                'Rated' => $movie->Rated,
                'Description' => $movie->Description,
                'Genre' => $movie->genres->pluck('Name')->implode(', '),
                'Directors' => $movie->directors->pluck('Name')->all(),
                'Actors' => $movie->actors->pluck('Name')->all(),
            ]);

        return ['success' => true, 'data' => $movies];
    }

    public function getDatesByCinemaAndMovie($cinemaId, $movieId)
    {
        if (!Cinema::find($cinemaId) || !Movie::find($movieId)) {
            return ['success' => false, 'message' => 'Không tìm thấy rạp hoặc phim'];
        }

        $dates = Showtime::whereHas('room', fn($q) => $q->where('CinemaId', $cinemaId))
            ->where('MovieId', $movieId)
            ->where('StartTime', '>=', now())
            ->where('StartTime', '<=', now()->addDays(7))
            ->where('Status', 'Scheduled')
            ->selectRaw('DATE(StartTime) as date')
            ->distinct()
            ->orderBy('date', 'asc')
            ->pluck('date')
            ->values();

        return ['success' => true, 'data' => $dates];
    }

    public function getShowtimesByMovieAndDate($cinemaId, $movieId, $date)
    {
        if (!Cinema::find($cinemaId) || !Movie::find($movieId)) {
            return ['success' => false, 'message' => 'Không tìm thấy rạp hoặc phim'];
        }

        $showtimes = Showtime::where('MovieId', $movieId)
            ->whereHas('room', fn($q) => $q->where('CinemaId', $cinemaId))
            ->whereDate('StartTime', $date)
            ->where('StartTime', '>=', now())
            ->where('Status', 'Scheduled')
            ->with(['room'])
            ->orderBy('StartTime', 'asc')
            ->get()
            ->map(fn($showtime) => [
                'ShowtimeId' => $showtime->ShowtimeId,
                'StartTime' => $showtime->StartTime->format('H:i'),
                'EndTime' => $showtime->EndTime->format('H:i'),
                'Room' => $showtime->room->Name,
                'RoomType' => $showtime->room->RoomType,
                'Price' => $showtime->Price,
            ]);

        return ['success' => true, 'data' => $showtimes];
    }

    private function generateShowtimesFromSchedules($cinemaId, $date)
    {
        $carbon = Carbon::parse($date);
        $dayOfWeek = $carbon->dayOfWeek;
        $dayMap = [0 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
        $dayValue = $dayMap[$dayOfWeek];

        $schedules = Schedule::whereHas('room', fn($q) => $q->where('CinemaId', $cinemaId))
            ->where('Status', 'Active')
            ->where('StartDate', '<=', $date)
            ->where(fn($q) => $q->whereNull('EndDate')->orWhere('EndDate', '>=', $date))
            ->get()
            ->filter(fn($schedule) => in_array($dayValue, array_map('trim', explode(',', $schedule->DaysOfWeek))));

        foreach ($schedules as $schedule) {
            $startTime = Carbon::parse($date . ' ' . $schedule->StartTime->format('H:i:s'));
            $endTime = Carbon::parse($date . ' ' . $schedule->EndTime->format('H:i:s'));

            $exists = Showtime::where('RoomId', $schedule->RoomId)
                ->where('MovieId', $schedule->MovieId)
                ->where('StartTime', $startTime)
                ->exists();

            if (!$exists) {
                Showtime::create([
                    'MovieId' => $schedule->MovieId,
                    'RoomId' => $schedule->RoomId,
                    'StartTime' => $startTime,
                    'EndTime' => $endTime,
                    'Price' => $schedule->Price,
                    'Status' => 'Scheduled',
                    'CreatedAt' => now(),
                    'UpdatedAt' => now(),
                    'CreatedBy' => $schedule->CreatedBy,
                    'UpdatedBy' => $schedule->UpdatedBy,
                ]);
            }
        }
    }

    public function clearCache($id = null)
    {
        Cache::forget('cinemas_all');
        Cache::forget('cinemas_with_relations');
        if ($id) {
            Cache::forget("cinema_detail_{$id}");
            Cache::forget("cinema_detail_rel_{$id}");
        }
    }
}