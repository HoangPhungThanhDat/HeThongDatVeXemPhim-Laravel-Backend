<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Resources\MovieResource;
use App\Services\MovieService;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Cloudinary\Cloudinary;
use Illuminate\Support\Facades\Log;

/**
 * @group Movies
 * 
 * APIs for managing movies. Includes CRUD operations, 
 * filtering by status (Now Showing, Coming Soon, Ended),
 * search, and statistics.
 */
class MovieController extends Controller
{
    protected $movieService;
    protected $cloudinary;

    public function __construct(MovieService $movieService)
    {
        $this->movieService = $movieService;
        
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

        $this->middleware(['auth:api', 'checkrole:Admin'])
            ->only(['store', 'update', 'destroy']);
    }

    // ==================== CRUD CƠ BẢN ====================

    /**
     * Get all movies
     * 
     * Get a list of all movies in the system.
     * 
     * @unauthenticated
     * 
     * @response 200 [
     *   {
     *     "MovieId": 1,
     *     "Title": "Avengers: Endgame",
     *     "Description": "After the devastating events of Infinity War...",
     *     "PosterUrl": "https://res.cloudinary.com/.../movies/posters/avengers.jpg",
     *     "ReleaseDate": "2019-04-26",
     *     "Duration": 180,
     *     "Language": "English",
     *     "Rated": "PG-13",
     *     "Status": "NowShowing",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   }
     * ]
     */
    public function index(Request $request)
    {
        try {
            $movies = $this->movieService->getAll();
            if ($movies['success']) {
                return MovieResource::collection($movies['data']);
            }
            return response()->json($movies, 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get paginated movies
     * 
     * Get a paginated list of movies with search and filter options.
     * 
     * @unauthenticated
     * 
     * @queryParam page int The page number. Default: 1. Example: 1
     * @queryParam limit int Number of items per page. Default: 10. Max: 100. Example: 10
     * @queryParam search string Search by title or description. Example: Avengers
     * @queryParam status string Filter by status. Enum: NowShowing, ComingSoon, Ended. Example: NowShowing
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "MovieId": 1,
     *       "Title": "Avengers: Endgame",
     *       "PosterUrl": "https://...",
     *       "Status": "NowShowing"
     *     }
     *   ],
     *   "total": 100,
     *   "page": 1,
     *   "limit": 10,
     *   "totalPages": 10
     * }
     */
    public function getPaged(Request $request): JsonResponse
    {
        $page   = max(1, (int) $request->query('page',  1));
        $limit  = min(100, max(1, (int) $request->query('limit', 10)));
        $search = $request->query('search', '');
        $status = $request->query('status', '');

        $query = Movie::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('Title', 'like', "%{$search}%")
                  ->orWhere('Description', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('Status', $status);
        }

        $total      = $query->count();
        $totalPages = (int) ceil($total / $limit);
        $items      = $query->orderBy('MovieId', 'desc')
                            ->skip(($page - 1) * $limit)
                            ->take($limit)
                            ->get();

        return response()->json([
            'data'       => MovieResource::collection($items),
            'total'      => $total,
            'page'       => $page,
            'limit'      => $limit,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Create a new movie
     * 
     * Create a new movie with poster upload to Cloudinary. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam Title string required The movie title. Example: Avengers: Endgame
     * @bodyParam Description string The movie description. Example: After the devastating events of Infinity War...
     * @bodyParam PosterUrl file The movie poster (jpg, png, webp). Max 2MB.
     * @bodyParam ReleaseDate date required The release date. Example: 2019-04-26
     * @bodyParam Duration int required The duration in minutes. Example: 180
     * @bodyParam Language string The language. Example: English
     * @bodyParam Rated string The rating. Enum: G, PG, PG-13, R, NC-17. Example: PG-13
     * @bodyParam Director string The director name. Example: Anthony Russo
     * @bodyParam Cast string The cast members. Example: Robert Downey Jr., Chris Evans
     * 
     * @response 201 {
     *   "MovieId": 1,
     *   "Title": "Avengers: Endgame",
     *   "Description": "After the devastating events of Infinity War...",
     *   "PosterUrl": "https://res.cloudinary.com/.../movies/posters/avengers.jpg",
     *   "ReleaseDate": "2019-04-26",
     *   "Duration": 180,
     *   "Status": "NowShowing"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "Title": ["The title field is required."],
     *     "ReleaseDate": ["The release date field is required."],
     *     "Duration": ["The duration field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Upload ảnh thất bại: ..."
     * }
     */
    public function store(StoreMovieRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('PosterUrl')) {
                try {
                    $file = $request->file('PosterUrl');
                    
                    $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                        'folder' => 'movies/posters',
                        'transformation' => [
                            'width' => 600,
                            'height' => 900,
                            'crop' => 'fill',
                            'quality' => 'auto:best',
                            'fetch_format' => 'auto'
                        ]
                    ]);
                    
                    $data['PosterUrl'] = $result['secure_url'];
                } catch (\Exception $e) {
                    Log::error('Upload ảnh movie thất bại: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Upload ảnh thất bại: ' . $e->getMessage()
                    ], 500);
                }
            } else {
                $data['PosterUrl'] = null;
            }

            $today       = now()->startOfDay();
            $releaseDate = \Carbon\Carbon::parse($data['ReleaseDate'])->startOfDay();
            $data['Status'] = $releaseDate->gt($today) ? 'ComingSoon' : 'NowShowing';

            $result = $this->movieService->createWithValidation($data);
            if (!$result['success']) return response()->json($result, 400);

            $movie = $result['data'];
            if ($movie && method_exists($movie, 'showtimes')) {
                $hasShowtime = $movie->showtimes()->where('StartTime', '>=', now())->exists();
                if (!$hasShowtime && $releaseDate->lt($today)) {
                    $movie->Status = 'Ended';
                    $movie->save();
                }
            }

            return new MovieResource($movie);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi tạo phim', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get a specific movie
     * 
     * Get detailed information about a specific movie.
     * 
     * @unauthenticated
     * 
     * @urlParam movieId int required The ID of the movie. Example: 1
     * 
     * @response 200 {
     *   "MovieId": 1,
     *   "Title": "Avengers: Endgame",
     *   "Description": "After the devastating events of Infinity War...",
     *   "PosterUrl": "https://...",
     *   "ReleaseDate": "2019-04-26",
     *   "Duration": 180,
     *   "Language": "English",
     *   "Rated": "PG-13",
     *   "Status": "NowShowing"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy phim"
     * }
     */
    public function show($movieId)
    {
        try {
            $result = $this->movieService->find($movieId);
            if (!$result['success']) return response()->json($result, 404);
            return new MovieResource($result['data']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a movie
     * 
     * Update an existing movie. Poster upload is optional. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam movieId int required The ID of the movie. Example: 1
     * @bodyParam Title string The movie title. Example: Avengers: Endgame - Director's Cut
     * @bodyParam Description string The movie description.
     * @bodyParam PosterUrl file The movie poster (jpg, png, webp). Max 2MB.
     * @bodyParam ReleaseDate date The release date. Example: 2019-04-26
     * @bodyParam Duration int The duration in minutes. Example: 180
     * @bodyParam Language string The language. Example: English
     * @bodyParam Rated string The rating. Enum: G, PG, PG-13, R, NC-17. Example: PG-13
     * @bodyParam Status string The status. Enum: NowShowing, ComingSoon, Ended. Example: NowShowing
     * 
     * @response 200 {
     *   "MovieId": 1,
     *   "Title": "Avengers: Endgame - Director's Cut",
     *   "Status": "NowShowing"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy phim"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Upload ảnh thất bại: ..."
     * }
     */
    public function update(StoreMovieRequest $request, $movieId)
    {
        try {
            $data        = $request->validated();
            $movieResult = $this->movieService->find($movieId);
            if (!$movieResult['success']) return response()->json($movieResult, 404);
            $movie = $movieResult['data'];

            if ($request->hasFile('PosterUrl')) {
                // Xóa ảnh cũ trên Cloudinary
                if ($movie->PosterUrl && strpos($movie->PosterUrl, 'cloudinary.com') !== false) {
                    try {
                        $publicId = $this->extractPublicId($movie->PosterUrl);
                        if ($publicId) {
                            $this->cloudinary->uploadApi()->destroy($publicId);
                        }
                    } catch (\Exception $e) {
                        Log::error('Xóa ảnh Cloudinary thất bại: ' . $e->getMessage());
                    }
                }
                
                // Upload ảnh mới
                try {
                    $file = $request->file('PosterUrl');
                    $result = $this->cloudinary->uploadApi()->upload($file->getRealPath(), [
                        'folder' => 'movies/posters',
                        'transformation' => [
                            'width' => 600,
                            'height' => 900,
                            'crop' => 'fill',
                            'quality' => 'auto:best',
                            'fetch_format' => 'auto'
                        ]
                    ]);
                    
                    $data['PosterUrl'] = $result['secure_url'];
                } catch (\Exception $e) {
                    Log::error('Upload ảnh movie thất bại: ' . $e->getMessage());
                    return response()->json([
                        'success' => false,
                        'message' => 'Upload ảnh thất bại: ' . $e->getMessage()
                    ], 500);
                }
            } else {
                $data['PosterUrl'] = $movie->PosterUrl;
            }

            $today       = now()->startOfDay();
            $releaseDate = \Carbon\Carbon::parse($data['ReleaseDate'])->startOfDay();
            $data['Status'] = $releaseDate->gt($today) ? 'ComingSoon' : 'NowShowing';

            $result = $this->movieService->updateWithValidation($movieId, $data);
            if (!$result['success']) return response()->json($result, 400);

            $updatedMovie = $result['data'];
            if ($updatedMovie && method_exists($updatedMovie, 'showtimes')) {
                $hasShowtime = $updatedMovie->showtimes()->where('StartTime', '>=', now())->exists();
                if (!$hasShowtime && $releaseDate->lt($today)) {
                    $updatedMovie->Status = 'Ended';
                    $updatedMovie->save();
                }
            }

            return new MovieResource($updatedMovie);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi cập nhật phim', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a movie
     * 
     * Delete a movie and remove its poster from Cloudinary. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam movieId int required The ID of the movie. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Xóa phim thành công"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy phim"
     * }
     */
    public function destroy($movieId)
    {
        try {
            $movie = Movie::find($movieId);
            
            // Xóa ảnh trên Cloudinary
            if ($movie && $movie->PosterUrl && strpos($movie->PosterUrl, 'cloudinary.com') !== false) {
                try {
                    $publicId = $this->extractPublicId($movie->PosterUrl);
                    if ($publicId) {
                        $this->cloudinary->uploadApi()->destroy($publicId);
                    }
                } catch (\Exception $e) {
                    Log::error('Xóa ảnh Cloudinary thất bại: ' . $e->getMessage());
                }
            }
            
            $result = $this->movieService->delete($movieId);
            if (!$result['success']) return response()->json($result, 404);
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa phim', 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== PHIM ĐANG CHIẾU ====================

    /**
     * Get now showing movies
     * 
     * Get all movies currently showing in cinemas.
     * 
     * @unauthenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "MovieId": 1,
     *       "Title": "Avengers: Endgame",
     *       "PosterUrl": "https://...",
     *       "Status": "NowShowing"
     *     }
     *   ]
     * }
     */
    public function getNowShowing(): JsonResponse
    {
        try {
            $result = $this->movieService->getNowShowingMovies();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get movie detail
     * 
     * Get detailed information about a specific movie with additional data.
     * 
     * @unauthenticated
     * 
     * @urlParam movieId int required The ID of the movie. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "MovieId": 1,
     *     "Title": "Avengers: Endgame",
     *     "Description": "...",
     *     "PosterUrl": "https://...",
     *     "Genres": ["Action", "Adventure"],
     *     "Cast": ["Robert Downey Jr.", "Chris Evans"],
     *     "Showtimes": [...]
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy phim"
     * }
     */
    public function getDetail($movieId): JsonResponse
    {
        try {
            $result     = $this->movieService->getMovieDetail($movieId);
            $statusCode = $result['success'] ? 200 : 404;
            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== PHIM SẮP CHIẾU ====================

    /**
     * Get coming soon movies
     * 
     * Get all movies that are coming soon to cinemas.
     * 
     * @unauthenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [
     *     {
     *       "MovieId": 2,
     *       "Title": "Dune: Part Two",
     *       "PosterUrl": "https://...",
     *       "ReleaseDate": "2024-03-01",
     *       "Status": "ComingSoon"
     *     }
     *   ]
     * }
     */
    public function getComingSoon(): JsonResponse
    {
        try {
            $result = $this->movieService->getComingSoonMovies();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== LỌC & TÌM KIẾM ====================

    /**
     * Get now showing filters
     * 
     * Get available filter options for now showing movies (genres, directors, etc.).
     * 
     * @unauthenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "genres": ["Action", "Comedy", "Drama"],
     *     "directors": ["Anthony Russo", "Christopher Nolan"],
     *     "languages": ["English", "Vietnamese"],
     *     "rated": ["PG", "PG-13", "R"]
     *   }
     * }
     */
    public function getNowShowingFilters(): JsonResponse
    {
        try {
            $result = $this->movieService->getNowShowingFilters();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Filter now showing movies
     * 
     * Filter now showing movies by genre, director, actor, language, or rating.
     * 
     * @unauthenticated
     * 
     * @bodyParam genre_id int Filter by genre ID. Example: 1
     * @bodyParam director_name string Filter by director name. Example: Christopher Nolan
     * @bodyParam actor_name string Filter by actor name. Example: Robert Downey Jr.
     * @bodyParam language string Filter by language. Example: English
     * @bodyParam rated string Filter by rating. Example: PG-13
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     */
    public function filterNowShowing(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['genre_id', 'director_name', 'actor_name', 'language', 'rated']);
            $result  = $this->movieService->filterNowShowing($filters);
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get now showing movies by genre
     * 
     * Get all now showing movies filtered by genre.
     * 
     * @unauthenticated
     * 
     * @urlParam genreId int required The ID of the genre. Example: 1
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Không tìm thấy thể loại phim"
     * }
     */
    public function getNowShowingByGenre($genreId): JsonResponse
    {
        try {
            $result = $this->movieService->getNowShowingByGenre($genreId);
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Search now showing movies
     * 
     * Search for now showing movies by keyword.
     * 
     * @unauthenticated
     * 
     * @queryParam keyword string required The search keyword. Example: Avengers
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Vui lòng nhập từ khóa tìm kiếm"
     * }
     */
    public function searchNowShowing(Request $request): JsonResponse
    {
        try {
            $keyword = $request->input('keyword', '');
            if (empty($keyword)) {
                return response()->json(['success' => false, 'message' => 'Vui lòng nhập từ khóa tìm kiếm'], 400);
            }
            $result = $this->movieService->searchNowShowing($keyword);
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== PHIM HOT & MỚI NHẤT ====================

    /**
     * Get hot movies
     * 
     * Get the most popular/hot movies based on ticket sales or views.
     * 
     * @unauthenticated
     * 
     * @queryParam limit int Number of movies to return. Default: 10. Example: 10
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     */
    public function getHotMovies(Request $request): JsonResponse
    {
        try {
            $result = $this->movieService->getHotMovies($request->input('limit', 10));
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get latest movies
     * 
     * Get the most recently added movies.
     * 
     * @unauthenticated
     * 
     * @queryParam limit int Number of movies to return. Default: 10. Example: 10
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     */
    public function getLatestMovies(Request $request): JsonResponse
    {
        try {
            $result = $this->movieService->getLatestMovies($request->input('limit', 10));
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== THỐNG KÊ ====================

    /**
     * Get movie statistics
     * 
     * Get statistics about movies including total count, by status, etc.
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "total": 50,
     *     "now_showing": 10,
     *     "coming_soon": 15,
     *     "ended": 25,
     *     "total_showtimes": 120,
     *     "total_reviews": 500,
     *     "average_rating": 4.5
     *   }
     * }
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $result = $this->movieService->getStatistics();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get movies by status
     * 
     * Get movies filtered by status.
     * 
     * @unauthenticated
     * 
     * @urlParam status string required The movie status. Enum: NowShowing, ComingSoon, Ended. Example: NowShowing
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     * @response 400 {
     *   "success": false,
     *   "message": "Trạng thái không hợp lệ. Chỉ chấp nhận: NowShowing, ComingSoon, Ended"
     * }
     */
    public function getByStatus($status): JsonResponse
    {
        try {
            $validStatuses = ['NowShowing', 'ComingSoon', 'Ended'];
            if (!in_array($status, $validStatuses)) {
                return response()->json(['success' => false, 'message' => 'Trạng thái không hợp lệ. Chỉ chấp nhận: ' . implode(', ', $validStatuses)], 400);
            }
            $result = $this->movieService->getByStatus($status);
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
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