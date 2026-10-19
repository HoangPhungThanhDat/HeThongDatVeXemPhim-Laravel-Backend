<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Resources\MovieResource;
use App\Services\MovieService;
use App\Models\Movie;
use App\Models\Moviecast;
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
            ->only(['store', 'update', 'destroy', 'autoUpdateStatus', 'updateSingleStatus']);
    }

    // ==================== PHƯƠNG THỨC HỖ TRỢ CAST MEMBERS ====================

    private function saveCastMembers($movieId, array $castMembers): array
    {
        if (empty($castMembers)) {
            return [];
        }

        $results = [];

        foreach ($castMembers as $member) {
            if (empty($member['name']) || empty($member['role'])) {
                continue;
            }

            try {
                $existing = Moviecast::where('MovieId', $movieId)
                    ->where('Name', trim($member['name']))
                    ->where('Role', $member['role'])
                    ->first();

                if ($existing) {
                    $results[] = $existing;
                    continue;
                }

                $data = [
                    'MovieId' => $movieId,
                    'Name' => trim($member['name']),
                    'Role' => $member['role'],
                    'Status' => 'Active',
                    'CreatedBy' => null,
                    'UpdatedBy' => null,
                ];

                $cast = Moviecast::create($data);
                $results[] = $cast;
            } catch (\Exception $e) {
                Log::error("Lỗi khi lưu cast member '{$member['name']}': " . $e->getMessage());
            }
        }

        return $results;
    }

    private function deleteCastMembers($movieId): int
    {
        try {
            return Moviecast::where('MovieId', $movieId)->delete();
        } catch (\Exception $e) {
            Log::error("Lỗi khi xóa cast members của phim {$movieId}: " . $e->getMessage());
            return 0;
        }
    }

    private function parseCastStringToArray(?string $castString, string $defaultRole = 'Actor'): array
    {
        if (empty($castString)) {
            return [];
        }

        $names = array_filter(array_map('trim', explode(',', $castString)));
        $result = [];
        foreach ($names as $name) {
            $result[] = [
                'name' => $name,
                'role' => $defaultRole,
            ];
        }
        return $result;
    }

    // ==================== AUTO UPDATE STATUS ====================

    /**
     * ✅ Tự động cập nhật trạng thái cho tất cả phim
     * 
     * @authenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Đã cập nhật trạng thái cho 5 phim",
     *   "updated_count": 5,
     *   "updates": [...]
     * }
     */
    public function autoUpdateStatus(): JsonResponse
    {
        try {
            $result = $this->movieService->autoUpdateStatus();
            
            if ($result['success']) {
                return response()->json($result, 200);
            }
            
            return response()->json($result, 500);
        } catch (\Exception $e) {
            Log::error('Lỗi autoUpdateStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái phim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ Cập nhật trạng thái cho một phim cụ thể
     * 
     * @authenticated
     * 
     * @urlParam movieId int required ID của phim cần cập nhật
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {...},
     *   "changed": true,
     *   "old_status": "ComingSoon",
     *   "new_status": "NowShowing"
     * }
     */
    public function updateSingleStatus($movieId): JsonResponse
    {
        try {
            $result = $this->movieService->updateSingleStatus((int)$movieId);
            
            if ($result['success']) {
                return response()->json($result, 200);
            }
            
            return response()->json($result, 404);
        } catch (\Exception $e) {
            Log::error('Lỗi updateSingleStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== CRUD CƠ BẢN ====================

    /**
     * Get all movies
     * 
     * @unauthenticated
     * 
     * @response 200 [...]
     */
    public function index(Request $request)
    {
        try {
            $movies = $this->movieService->getAll();
            if ($movies['success']) {
                $movies['data']->load('castMembers');
                return MovieResource::collection($movies['data']);
            }
            return response()->json($movies, 500);
        } catch (\Exception $e) {
            Log::error('Error in index: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi lấy danh sách phim', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated movies
     * 
     * @unauthenticated
     * 
     * @queryParam page int The page number. Default: 1.
     * @queryParam limit int Number of items per page. Default: 10. Max: 100.
     * @queryParam search string Search by title or description.
     * @queryParam status string Filter by status. Enum: NowShowing, ComingSoon, Ended.
     * 
     * @response 200 {
     *   "data": [...],
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

        try {
            // ✅ Gọi qua Service (đã có Redis Cache trong Repository)
            $result = $this->movieService->getPaged($page, $limit, $search, $status);

            return response()->json([
                'data'       => MovieResource::collection($result['data']),
                'total'      => $result['total'],
                'page'       => $result['page'],
                'limit'      => $result['limit'],
                'totalPages' => $result['totalPages'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getPaged: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi lấy danh sách phim', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new movie
     * 
     * @authenticated
     * 
     * @bodyParam Title string required The movie title.
     * @bodyParam Description string The movie description.
     * @bodyParam PosterUrl file The movie poster (jpg, png, webp). Max 2MB.
     * @bodyParam ReleaseDate date required The release date.
     * @bodyParam Duration int required The duration in minutes.
     * @bodyParam Language string The language.
     * @bodyParam Rated string The rating. Enum: G, PG, PG-13, R, NC-17.
     * @bodyParam Director string The director name.
     * @bodyParam Cast string The cast members (comma separated) - Legacy format.
     * @bodyParam CastMembers array The cast members list (new format).
     * @bodyParam CastMembers[].name string The cast member name.
     * @bodyParam CastMembers[].role string The role (Actor, Director, Producer, Writer).
     * 
     * @response 201 {
     *   "MovieId": 1,
     *   "Title": "Avengers: Endgame",
     *   "castMembers": [...]
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

            $castMembers = $request->input('CastMembers', []);
            
            if (empty($castMembers) && $request->has('Cast')) {
                $castMembers = $this->parseCastStringToArray($request->input('Cast', ''));
            }

            if ($request->has('Director') && !empty($request->input('Director'))) {
                $directorName = trim($request->input('Director'));
                $hasDirector = false;
                foreach ($castMembers as $member) {
                    if (isset($member['role']) && $member['role'] === 'Director' && trim($member['name']) === $directorName) {
                        $hasDirector = true;
                        break;
                    }
                }
                if (!$hasDirector) {
                    $castMembers[] = [
                        'name' => $directorName,
                        'role' => 'Director',
                    ];
                }
            }

            $result = $this->movieService->createWithValidation($data);
            if (!$result['success']) {
                return response()->json($result, 400);
            }

            $movie = $result['data'];
            
            if ($movie && method_exists($movie, 'updateStatus')) {
                $movie->updateStatus();
            }

            if (!empty($castMembers)) {
                $this->saveCastMembers($movie->MovieId, $castMembers);
            }

            $movie->load('castMembers');

            return new MovieResource($movie);
        } catch (\Exception $e) {
            Log::error('Error in store: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi tạo phim', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific movie
     * 
     * @unauthenticated
     * 
     * @urlParam movieId int required The ID of the movie.
     * 
     * @response 200 {
     *   "MovieId": 1,
     *   "Title": "Avengers: Endgame",
     *   "castMembers": [...]
     * }
     */
    public function show($movieId)
    {
        try {
            $result = $this->movieService->find($movieId);
            if (!$result['success']) {
                return response()->json($result, 404);
            }
            
            $movie = $result['data'];
            $movie->load('castMembers');
            
            return new MovieResource($movie);
        } catch (\Exception $e) {
            Log::error('Error in show: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi lấy thông tin phim', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a movie
     * 
     * @authenticated
     * 
     * @urlParam movieId int required The ID of the movie.
     * @bodyParam CastMembers array The cast members list.
     * @bodyParam CastMembers[].name string The cast member name.
     * @bodyParam CastMembers[].role string The role (Actor, Director, Producer, Writer).
     * 
     * @response 200 {
     *   "MovieId": 1,
     *   "Title": "Avengers: Endgame - Director's Cut",
     *   "castMembers": [...]
     * }
     */
    public function update(StoreMovieRequest $request, $movieId)
    {
        try {
            $data = $request->validated();
            
            $movieResult = $this->movieService->find($movieId);
            if (!$movieResult['success']) {
                return response()->json($movieResult, 404);
            }
            $movie = $movieResult['data'];

            if ($request->hasFile('PosterUrl')) {
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

            $castMembers = $request->input('CastMembers', []);
            
            if (empty($castMembers) && $request->has('Cast')) {
                $castMembers = $this->parseCastStringToArray($request->input('Cast', ''));
            }

            if ($request->has('Director') && !empty($request->input('Director'))) {
                $directorName = trim($request->input('Director'));
                $hasDirector = false;
                foreach ($castMembers as $member) {
                    if (isset($member['role']) && $member['role'] === 'Director' && trim($member['name']) === $directorName) {
                        $hasDirector = true;
                        break;
                    }
                }
                if (!$hasDirector) {
                    $castMembers[] = [
                        'name' => $directorName,
                        'role' => 'Director',
                    ];
                }
            }

            $result = $this->movieService->updateWithValidation($movieId, $data);
            if (!$result['success']) {
                return response()->json($result, 400);
            }

            $updatedMovie = $result['data'];
            
            if ($updatedMovie && method_exists($updatedMovie, 'updateStatus')) {
                $updatedMovie->updateStatus();
            }

            $this->deleteCastMembers($movieId);
            
            if (!empty($castMembers)) {
                $this->saveCastMembers($movieId, $castMembers);
            }

            $updatedMovie->load('castMembers');

            return new MovieResource($updatedMovie);
        } catch (\Exception $e) {
            Log::error('Error in update: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi cập nhật phim', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a movie
     * 
     * @authenticated
     * 
     * @urlParam movieId int required The ID of the movie.
     * 
     * @response 200 {
     *   "success": true,
     *   "message": "Xóa phim và các diễn viên liên quan thành công"
     * }
     */
    public function destroy($movieId)
    {
        try {
            $movie = Movie::find($movieId);
            
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
            
            $castCount = 0;
            if ($movie) {
                $castCount = $this->deleteCastMembers($movieId);
                Log::info("Đã xóa {$castCount} cast members của phim {$movieId}");
            }
            
            $result = $this->movieService->delete($movieId);
            if (!$result['success']) {
                return response()->json($result, 404);
            }
            
            return response()->json([
                'success' => true,
                'message' => "Xóa phim và {$castCount} diễn viên/đạo diễn liên quan thành công"
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error in destroy: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi xóa phim', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== PHIM ĐANG CHIẾU ====================

    /**
     * Get now showing movies
     * 
     * @unauthenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     */
    public function getNowShowing(): JsonResponse
    {
        try {
            $result = $this->movieService->getNowShowingMovies();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Error in getNowShowing: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi lấy danh sách phim đang chiếu', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get movie detail
     * 
     * @unauthenticated
     * 
     * @urlParam movieId int required The ID of the movie.
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "MovieId": 1,
     *     "Title": "Avengers: Endgame",
     *     "castMembers": [...]
     *   }
     * }
     */
    public function getDetail($movieId): JsonResponse
    {
        try {
            $result = $this->movieService->getMovieDetail($movieId);
            $statusCode = $result['success'] ? 200 : 404;
            
            if ($result['success'] && isset($result['data'])) {
                $result['data']->load('castMembers');
            }
            
            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            Log::error('Error in getDetail: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi lấy chi tiết phim', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== PHIM SẮP CHIẾU ====================

    /**
     * Get coming soon movies
     * 
     * @unauthenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     */
    public function getComingSoon(): JsonResponse
    {
        try {
            $result = $this->movieService->getComingSoonMovies();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Error in getComingSoon: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi lấy danh sách phim sắp chiếu', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== LỌC & TÌM KIẾM ====================

    /**
     * Get now showing filters
     * 
     * @unauthenticated
     * 
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "genres": [...],
     *     "directors": [...],
     *     "languages": [...],
     *     "rated": [...]
     *   }
     * }
     */
    public function getNowShowingFilters(): JsonResponse
    {
        try {
            $result = $this->movieService->getNowShowingFilters();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Error in getNowShowingFilters: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi lấy filters', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Filter now showing movies
     * 
     * @unauthenticated
     * 
     * @bodyParam genre_id int Filter by genre ID.
     * @bodyParam director_name string Filter by director name.
     * @bodyParam actor_name string Filter by actor name.
     * @bodyParam language string Filter by language.
     * @bodyParam rated string Filter by rating.
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
            Log::error('Error in filterNowShowing: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi lọc phim', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get now showing movies by genre
     * 
     * @unauthenticated
     * 
     * @urlParam genreId int required The ID of the genre.
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     */
    public function getNowShowingByGenre($genreId): JsonResponse
    {
        try {
            $result = $this->movieService->getNowShowingByGenre($genreId);
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Error in getNowShowingByGenre: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi lấy phim theo thể loại', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search now showing movies
     * 
     * @unauthenticated
     * 
     * @queryParam keyword string required The search keyword.
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     */
    public function searchNowShowing(Request $request): JsonResponse
    {
        try {
            $keyword = $request->input('keyword', '');
            if (empty($keyword)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Vui lòng nhập từ khóa tìm kiếm'
                ], 400);
            }
            $result = $this->movieService->searchNowShowing($keyword);
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Error in searchNowShowing: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi tìm kiếm phim', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== PHIM HOT & MỚI NHẤT ====================

    /**
     * Get hot movies
     * 
     * @unauthenticated
     * 
     * @queryParam limit int Number of movies to return. Default: 10.
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
            Log::error('Error in getHotMovies: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi lấy phim hot', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest movies
     * 
     * @unauthenticated
     * 
     * @queryParam limit int Number of movies to return. Default: 10.
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
            Log::error('Error in getLatestMovies: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi lấy phim mới nhất', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== THỐNG KÊ ====================

    /**
     * Get movie statistics
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
     *     "average_rating": 4.5,
     *     "total_cast_members": 120
     *   }
     * }
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $result = $this->movieService->getStatistics();
            
            if ($result['success']) {
                $totalCast = Moviecast::count();
                $result['data']['total_cast_members'] = $totalCast;
            }
            
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Error in getStatistics: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi lấy thống kê', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get movies by status
     * 
     * @unauthenticated
     * 
     * @urlParam status string required The movie status. Enum: NowShowing, ComingSoon, Ended.
     * 
     * @response 200 {
     *   "success": true,
     *   "data": [...]
     * }
     */
    public function getByStatus($status): JsonResponse
    {
        try {
            $validStatuses = ['NowShowing', 'ComingSoon', 'Ended'];
            if (!in_array($status, $validStatuses)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Trạng thái không hợp lệ. Chỉ chấp nhận: ' . implode(', ', $validStatuses)
                ], 400);
            }
            
            $result = $this->movieService->getByStatus($status);
            
            if ($result['success'] && isset($result['data'])) {
                $result['data']->load('castMembers');
            }
            
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            Log::error('Error in getByStatus: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Có lỗi xảy ra khi lấy phim theo trạng thái', 
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== PRIVATE HELPERS ====================

    private function extractPublicId($url)
    {
        preg_match('/\/upload\/v\d+\/(.+)\.\w+$/', $url, $matches);
        return $matches[1] ?? null;
    }
}