<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCinemaRequest;
use App\Http\Resources\CinemaResource;
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

    public function index()
    {
        $cinemas = $this->cinemaService->getAll();
        return CinemaResource::collection($cinemas);
    }

    public function store(StoreCinemaRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('ImageUrl')) {
            $file      = $request->file('ImageUrl');
            $hash      = md5_file($file->getRealPath());
            $extension = $file->getClientOriginalExtension();
            $fileName  = $hash . '.' . $extension;
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
        return new CinemaResource($cinema);
    }

    public function show($CinemaId)
    {
        $cinema = $this->cinemaService->getById($CinemaId);
        return new CinemaResource($cinema);
    }

    public function update(StoreCinemaRequest $request, $CinemaId)
    {
        $data   = $request->validated();
        $cinema = $this->cinemaService->getById($CinemaId);

        if ($request->hasFile('ImageUrl')) {
            $file      = $request->file('ImageUrl');
            $hash      = md5_file($file->getRealPath());
            $extension = $file->getClientOriginalExtension();
            $fileName  = $hash . '.' . $extension;
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
        return new CinemaResource($cinema);
    }

    public function destroy($CinemaId)
    {
        $this->cinemaService->delete($CinemaId);
        return response()->json(['message' => 'Cinema deleted successfully']);
    }

    // ✅ API lịch chiếu theo rạp + ngày
    public function showtimes($cinemaId, Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        $showtimes = $this->cinemaService->getShowtimes($cinemaId, $date);

        // ✅ Trả về [] thay vì lỗi nếu không có suất chiếu
        if ($showtimes->isEmpty()) {
            return response()->json([
                'cinemaId' => $cinemaId,
                'date'     => $date,
                'movies'   => []
            ]);
        }

        $movies = $showtimes->groupBy('MovieId')->map(function ($items) {

            $movie = $items->first()->movie;

            // ✅ Guard: bỏ qua nếu movie bị null (dữ liệu lỗi)
            if (!$movie) return null;

            return [
                'MovieId'     => $movie->MovieId,
                'Title'       => $movie->Title,
                'PosterUrl'   => $movie->PosterUrl,
                'Duration'    => $movie->Duration,
                'Description' => $movie->Description,
                // ✅ directors & actors đã được eager load sẵn → không query thêm
                'Directors'   => $movie->directors->pluck('Name')->all(),
                'Actors'      => $movie->actors->pluck('Name')->all(),
                'showtimes'   => $items->map(function ($showtime) {
                    return [
                        'ShowtimeId' => $showtime->ShowtimeId,
                        'StartTime'  => $showtime->StartTime->format('H:i'),
                        'EndTime'    => $showtime->EndTime->format('H:i'),
                        'Room'       => $showtime->room->Name,
                        'RoomType'   => $showtime->room->RoomType,
                        'Price'      => $showtime->Price,
                    ];
                })->values()
            ];

        // ✅ Lọc bỏ các phần tử null (movie không tồn tại)
        })->filter()->values();

        return response()->json([
            'cinemaId' => $cinemaId,
            'date'     => $date,
            'movies'   => $movies
        ]);
    }
    /**
 * API: Lấy danh sách rạp chiếu 1 phim và các suất chiếu trong ngày
 */
// CinemaController.php
public function showtimesByMovie($movieId)
{
    $data = $this->cinemaService->getShowtimesGroupedByCity($movieId);

    return response()->json([
        'MovieId' => $movieId,
        'CinemasByCity' => $data
    ]);
}
}