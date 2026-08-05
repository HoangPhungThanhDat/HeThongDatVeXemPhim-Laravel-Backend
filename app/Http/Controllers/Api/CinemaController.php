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
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;

/**
 * @group Cinemas
 * 
 * APIs for managing cinemas. Includes cinema CRUD operations,
 * room management, showtimes, and statistics.
 */
class CinemaController extends Controller
{
    protected $cinemaService;
    protected $cloudinary;

    public function __construct(CinemaService $cinemaService)
    {
        $this->cinemaService = $cinemaService;
        
        // Khởi tạo Cloudinary
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => config('cloudinary.cloud_name'),
                'api_key' => config('cloudinary.api_key'),
                'api_secret' => config('cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => config('cloudinary.secure', true),
            ],
        ]);
    }

    /**
     * Get all cinemas
     * 
     * Get a list of all cinemas with their rooms and seats.
     * 
     * @unauthenticated
     * 
     * @response 200 [
     *   {
     *     "CinemaId": 1,
     *     "Name": "CGV Vincom Center",
     *     "Address": "72 Nguyễn Trãi, P. Bến Nghé, Q.1, TP.HCM",
     *     "City": "Hồ Chí Minh",
     *     "Phone": "028 12345678",
     *     "ImageUrl": "https://res.cloudinary.com/.../cinemas/cgv.jpg",
     *     "Status": "Active",
     *     "Description": "Cinema description",
     *     "Rooms": [...]
     *   }
     * ]
     */
    public function index()
    {
        $cinemas = $this->cinemaService->getAll(['rooms', 'rooms.seats']);
        return CinemaResource::collection($cinemas);
    }

    /**
     * Create a new cinema
     * 
     * Create a new cinema with image upload to Cloudinary. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam Name string required The cinema name. Example: CGV Vincom Center
     * @bodyParam Address string required The cinema address. Example: 72 Nguyễn Trãi, P. Bến Nghé, Q.1, TP.HCM
     * @bodyParam City string required The city. Example: Hồ Chí Minh
     * @bodyParam Phone string The phone number. Example: 028 12345678
     * @bodyParam ImageUrl file The cinema image (jpg, png, webp). Max 2MB.
     * @bodyParam Description string Cinema description.
     * @bodyParam Status string Status. Enum: Active, Inactive. Example: Active
     * 
     * @response 201 {
     *   "CinemaId": 1,
     *   "Name": "CGV Vincom Center",
     *   "Address": "72 Nguyễn Trãi, P. Bến Nghé, Q.1, TP.HCM",
     *   "City": "Hồ Chí Minh",
     *   "Phone": "028 12345678",
     *   "ImageUrl": "https://res.cloudinary.com/.../cinemas/cgv.jpg",
     *   "Status": "Active",
     *   "Rooms": []
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "Name": ["The name field is required."],
     *     "Address": ["The address field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Upload ảnh thất bại: ..."
     * }
     */
    public function store(StoreCinemaRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('ImageUrl')) {
            try {
                $file = $request->file('ImageUrl');
                
                $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'cinemas',
                    'transformation' => [
                        'width' => 800,
                        'height' => 400,
                        'crop' => 'fill',
                        'quality' => 'auto:best',
                        'fetch_format' => 'auto'
                    ]
                ]);
                
                $data['ImageUrl'] = $result['secure_url'];
            } catch (\Exception $e) {
                Log::error('Upload ảnh cinema thất bại: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Upload ảnh thất bại: ' . $e->getMessage()
                ], 500);
            }
        } else {
            $data['ImageUrl'] = null;
        }

        $cinema = $this->cinemaService->create($data);
        $cinema->load(['rooms', 'rooms.seats']);
        
        return new CinemaResource($cinema);
    }

    /**
     * Get a specific cinema
     * 
     * Get detailed information about a specific cinema with its rooms and seats.
     * 
     * @unauthenticated
     * 
     * @urlParam CinemaId int required The ID of the cinema. Example: 1
     * 
     * @response 200 {
     *   "CinemaId": 1,
     *   "Name": "CGV Vincom Center",
     *   "Address": "72 Nguyễn Trãi, P. Bến Nghé, Q.1, TP.HCM",
     *   "City": "Hồ Chí Minh",
     *   "Phone": "028 12345678",
     *   "ImageUrl": "https://res.cloudinary.com/.../cinemas/cgv.jpg",
     *   "Status": "Active",
     *   "Rooms": [...]
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy rạp chiếu"
     * }
     */
    public function show($CinemaId)
    {
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
     * Update a cinema
     * 
     * Update an existing cinema. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam CinemaId int required The ID of the cinema. Example: 1
     * @bodyParam Name string The cinema name. Example: CGV Vincom Center Updated
     * @bodyParam Address string The cinema address. Example: 72 Nguyễn Trãi, P. Bến Nghé, Q.1, TP.HCM
     * @bodyParam City string The city. Example: Hồ Chí Minh
     * @bodyParam Phone string The phone number. Example: 028 12345678
     * @bodyParam ImageUrl file The cinema image (jpg, png, webp). Max 2MB.
     * @bodyParam Description string Cinema description.
     * @bodyParam Status string Status. Enum: Active, Inactive. Example: Inactive
     * 
     * @response 200 {
     *   "CinemaId": 1,
     *   "Name": "CGV Vincom Center Updated",
     *   "Address": "72 Nguyễn Trãi, P. Bến Nghé, Q.1, TP.HCM",
     *   "City": "Hồ Chí Minh",
     *   "Status": "Inactive"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy rạp chiếu"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Upload ảnh thất bại: ..."
     * }
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

        if ($request->hasFile('ImageUrl')) {
            // Xóa ảnh cũ trên Cloudinary
            if ($cinema->ImageUrl && strpos($cinema->ImageUrl, 'cloudinary.com') !== false) {
                try {
                    $publicId = $this->extractPublicId($cinema->ImageUrl);
                    if ($publicId) {
                        $this->cloudinary->uploadApi()->destroy($publicId);
                    }
                } catch (\Exception $e) {
                    Log::error('Xóa ảnh Cloudinary thất bại: ' . $e->getMessage());
                }
            }
            
            // Upload ảnh mới
            try {
                $file = $request->file('ImageUrl');
                $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                    'folder' => 'cinemas',
                    'transformation' => [
                        'width' => 800,
                        'height' => 400,
                        'crop' => 'fill',
                        'quality' => 'auto:best',
                        'fetch_format' => 'auto'
                    ]
                ]);
                
                $data['ImageUrl'] = $result['secure_url'];
            } catch (\Exception $e) {
                Log::error('Upload ảnh cinema thất bại: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Upload ảnh thất bại: ' . $e->getMessage()
                ], 500);
            }
        } else {
            $data['ImageUrl'] = $cinema->ImageUrl;
        }

        $cinema = $this->cinemaService->update($CinemaId, $data);
        $cinema->load(['rooms', 'rooms.seats']);
        
        return new CinemaResource($cinema);
    }

    /**
     * Delete a cinema
     * 
     * Delete a cinema. Cinema must have no rooms to be deleted. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam CinemaId int required The ID of the cinema. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Xóa rạp chiếu thành công"
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Không thể xóa rạp vì còn phòng chiếu. Vui lòng xóa hết phòng chiếu trước."
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy rạp chiếu"
     * }
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

        // Xóa ảnh trên Cloudinary
        if ($cinema->ImageUrl && strpos($cinema->ImageUrl, 'cloudinary.com') !== false) {
            try {
                $publicId = $this->extractPublicId($cinema->ImageUrl);
                if ($publicId) {
                    $this->cloudinary->uploadApi()->destroy($publicId);
                }
            } catch (\Exception $e) {
                Log::error('Xóa ảnh Cloudinary thất bại: ' . $e->getMessage());
            }
        }

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
     * Get rooms by cinema
     * 
     * Get all rooms and seats for a specific cinema.
     * 
     * @unauthenticated
     * 
     * @urlParam cinemaId int required The ID of the cinema. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "RoomId": 1,
     *       "Name": "Room 1",
     *       "RoomType": "2D",
     *       "Capacity": 100,
     *       "Seats": [...]
     *     }
     *   ]
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy rạp chiếu"
     * }
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
     * Get cinema statistics
     * 
     * Get statistics for a specific cinema or all cinemas including total rooms, seats, etc.
     * 
     * @unauthenticated
     * 
     * @urlParam cinemaId int optional The ID of the cinema. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "CinemaId": 1,
     *     "Name": "CGV Vincom Center",
     *     "TotalRooms": 5,
     *     "ActiveRooms": 4,
     *     "TotalSeats": 500
     *   }
     * }
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
     * Get showtimes by cinema and date
     * 
     * Get all showtimes for a specific cinema on a given date, grouped by movie.
     * 
     * @unauthenticated
     * 
     * @urlParam cinemaId int required The ID of the cinema. Example: 1
     * @queryParam date string The date in YYYY-MM-DD format. Default: today. Example: 2024-01-01
     * 
     * @response 200 {
     *   "cinemaId": 1,
     *   "date": "2024-01-01",
     *   "movies": [
     *     {
     *       "MovieId": 1,
     *       "Title": "Avengers: Endgame",
     *       "PosterUrl": "https://...",
     *       "Duration": 180,
     *       "showtimes": [
     *         {
     *           "ShowtimeId": 1,
     *           "StartTime": "10:00",
     *           "EndTime": "13:00",
     *           "Room": "Room 1",
     *           "RoomType": "2D",
     *           "Price": 120000
     *         }
     *       ]
     *     }
     *   ]
     * }
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
     * Get cinemas by movie with showtimes
     * 
     * Get all cinemas showing a specific movie, grouped by city with showtimes.
     * 
     * @unauthenticated
     * 
     * @urlParam movieId int required The ID of the movie. Example: 1
     * 
     * @response 200 {
     *   "MovieId": 1,
     *   "CinemasByCity": [
     *     {
     *       "city": "Hồ Chí Minh",
     *       "cinemas": [
     *         {
     *           "cinemaId": 1,
     *           "name": "CGV Vincom Center",
     *           "showtimes": [...]
     *         }
     *       ]
     *     }
     *   ]
     * }
     */
    public function showtimesByMovie($movieId)
    {
        $data = $this->cinemaService->getShowtimesGroupedByCity($movieId);

        return response()->json([
            'MovieId' => $movieId,
            'CinemasByCity' => $data
        ]);
    }

    /**
     * Extract public ID from Cloudinary URL
     * 
     * @param string $url
     * @return string|null
     */
    private function extractPublicId($url)
    {
        preg_match('/\/upload\/v\d+\/(.+)\.\w+$/', $url, $matches);
        return $matches[1] ?? null;
    }
}