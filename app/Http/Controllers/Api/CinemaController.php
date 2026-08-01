<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCinemaRequest;
use App\Http\Resources\CinemaResource;
use App\Http\Resources\RoomResource;
use App\Models\Cinema;
use App\Models\Movie;
use App\Models\Room;
use App\Models\Showtime;
use App\Services\CinemaService;
use Illuminate\Http\Request;

class CinemaController extends Controller
{
    protected $cinemaService;

    public function __construct(CinemaService $cinemaService)
    {
        $this->cinemaService = $cinemaService;
    }

    /**
     * Lấy danh sách tất cả rạp chiếu
     * (Đã bao gồm rooms và seats thông qua CinemaResource)
     */
    public function index()
    {
        // ✅ Eager load rooms và seats để tránh N+1 query
        $cinemas = $this->cinemaService->getAll(['rooms', 'rooms.seats']);
        return CinemaResource::collection($cinemas);
    }

    /**
     * Tạo rạp chiếu mới
     */
    public function store(StoreCinemaRequest $request)
    {
        $data = $request->validated();

        // Xử lý upload ảnh
        if ($request->hasFile('ImageUrl')) {
            $file = $request->file('ImageUrl');
            $hash = md5_file($file->getRealPath());
            $extension = $file->getClientOriginalExtension();
            $fileName = $hash . '.' . $extension;
            $folderPath = storage_path('app/public/uploads/cinemas');

            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0775, true);
            }
            if (!file_exists($folderPath . '/' . $fileName)) {
                $file->storeAs('uploads/cinemas', $fileName, 'public');
            }

            $data['ImageUrl'] = asset('storage/uploads/cinemas/' . $fileName);
        } else {
            $data['ImageUrl'] = null;
        }

        $cinema = $this->cinemaService->create($data);
        
        // ✅ Load lại với relationships
        $cinema->load(['rooms', 'rooms.seats']);
        
        return new CinemaResource($cinema);
    }

    /**
     * Lấy chi tiết rạp chiếu theo ID
     */
    public function show($CinemaId)
    {
        // ✅ Eager load rooms và seats
        $cinema = $this->cinemaService->getById($CinemaId, ['rooms', 'rooms.seats']);
        
        if (!$cinema) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy rạp chiếu'
            ], 404);
        }
        
        return new CinemaResource($cinema);
    }

    /**
     * Cập nhật rạp chiếu
     */
    public function update(StoreCinemaRequest $request, $CinemaId)
    {
        $data = $request->validated();
        $cinema = $this->cinemaService->getById($CinemaId);

        if (!$cinema) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy rạp chiếu'
            ], 404);
        }

        // Xử lý upload ảnh
        if ($request->hasFile('ImageUrl')) {
            $file = $request->file('ImageUrl');
            $hash = md5_file($file->getRealPath());
            $extension = $file->getClientOriginalExtension();
            $fileName = $hash . '.' . $extension;
            $folderPath = storage_path('app/public/uploads/cinemas');

            if (!file_exists($folderPath)) {
                mkdir($folderPath, 0775, true);
            }
            if (!file_exists($folderPath . '/' . $fileName)) {
                $file->storeAs('uploads/cinemas', $fileName, 'public');
            }

            $data['ImageUrl'] = asset('storage/uploads/cinemas/' . $fileName);
        } else {
            $data['ImageUrl'] = $cinema->ImageUrl;
        }

        $cinema = $this->cinemaService->update($CinemaId, $data);
        
        // ✅ Load lại với relationships
        $cinema->load(['rooms', 'rooms.seats']);
        
        return new CinemaResource($cinema);
    }

    /**
     * Xóa rạp chiếu
     */
    public function destroy($CinemaId)
    {
        $cinema = $this->cinemaService->getById($CinemaId);
        
        if (!$cinema) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy rạp chiếu'
            ], 404);
        }

        // ✅ Kiểm tra xem rạp có phòng chiếu không
        if ($cinema->rooms()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa rạp vì còn phòng chiếu. Vui lòng xóa hết phòng chiếu trước.'
            ], 400);
        }

        $this->cinemaService->delete($CinemaId);
        
        return response()->json([
            'success' => true,
            'message' => 'Xóa rạp chiếu thành công'
        ]);
    }

    /**
     * API: Lấy danh sách phòng của rạp
     */
    public function getRooms($cinemaId)
    {
        $cinema = $this->cinemaService->getById($cinemaId, ['rooms', 'rooms.seats']);
        
        if (!$cinema) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy rạp chiếu'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => RoomResource::collection($cinema->rooms)
        ]);
    }

    /**
     * API: Lấy thống kê rạp
     */
    public function getStats($cinemaId = null)
    {
        if ($cinemaId) {
            $cinema = $this->cinemaService->getById($cinemaId, ['rooms', 'rooms.seats']);
            
            if (!$cinema) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy rạp chiếu'
                ], 404);
            }

            $totalRooms = $cinema->rooms->count();
            $totalSeats = 0;
            $activeRooms = 0;

            foreach ($cinema->rooms as $room) {
                $totalSeats += $room->seats->count();
                if ($room->Status === 'Active') {
                    $activeRooms++;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'CinemaId' => $cinema->CinemaId,
                    'Name' => $cinema->Name,
                    'TotalRooms' => $totalRooms,
                    'ActiveRooms' => $activeRooms,
                    'TotalSeats' => $totalSeats,
                ]
            ]);
        }

        // Stats cho tất cả rạp
        $cinemas = $this->cinemaService->getAll(['rooms', 'rooms.seats']);
        $stats = [];

        foreach ($cinemas as $cinema) {
            $totalRooms = $cinema->rooms->count();
            $totalSeats = 0;
            $activeRooms = 0;

            foreach ($cinema->rooms as $room) {
                $totalSeats += $room->seats->count();
                if ($room->Status === 'Active') {
                    $activeRooms++;
                }
            }

            $stats[] = [
                'CinemaId' => $cinema->CinemaId,
                'Name' => $cinema->Name,
                'TotalRooms' => $totalRooms,
                'ActiveRooms' => $activeRooms,
                'TotalSeats' => $totalSeats,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * API: Lịch chiếu theo rạp + ngày
     */
    public function showtimes($cinemaId, Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        $showtimes = $this->cinemaService->getShowtimes($cinemaId, $date);

        if ($showtimes->isEmpty()) {
            return response()->json([
                'cinemaId' => $cinemaId,
                'date' => $date,
                'movies' => []
            ]);
        }

        $movies = $showtimes->groupBy('MovieId')->map(function ($items) {
            $movie = $items->first()->movie;

            if (!$movie) return null;

            return [
                'MovieId' => $movie->MovieId,
                'Title' => $movie->Title,
                'PosterUrl' => $movie->PosterUrl,
                'Duration' => $movie->Duration,
                'Description' => $movie->Description,
                'Directors' => $movie->directors->pluck('Name')->all(),
                'Actors' => $movie->actors->pluck('Name')->all(),
                'showtimes' => $items->map(function ($showtime) {
                    return [
                        'ShowtimeId' => $showtime->ShowtimeId,
                        'StartTime' => $showtime->StartTime->format('H:i'),
                        'EndTime' => $showtime->EndTime->format('H:i'),
                        'Room' => $showtime->room->Name,
                        'RoomType' => $showtime->room->RoomType,
                        'Price' => $showtime->Price,
                    ];
                })->values()
            ];
        })->filter()->values();

        return response()->json([
            'cinemaId' => $cinemaId,
            'date' => $date,
            'movies' => $movies
        ]);
    }

    /**
     * API: Lấy danh sách rạp chiếu 1 phim và các suất chiếu trong ngày
     */
    public function showtimesByMovie($movieId)
    {
        $data = $this->cinemaService->getShowtimesGroupedByCity($movieId);

        return response()->json([
            'MovieId' => $movieId,
            'CinemasByCity' => $data
        ]);
    }
}