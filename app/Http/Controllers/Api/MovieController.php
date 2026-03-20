<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Resources\MovieResource;
use App\Services\MovieService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MovieController extends Controller
{
    protected $movieService;

    public function __construct(MovieService $movieService)
    {
        $this->movieService = $movieService;
        
        // Chỉ Admin mới được tạo, sửa, xóa phim
        $this->middleware(['auth:api', 'checkrole:Admin'])
            ->only(['store', 'update', 'destroy']);
    }

    // ==================== CRUD CƠ BẢN ====================

    /**
     * Lấy tất cả phim (có phân trang)
     * GET /api/movies?page=1&per_page=10
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $movies = $this->movieService->getAll();
            
            if ($movies['success']) {
                return MovieResource::collection($movies['data']);
            }
            
            return response()->json($movies, 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tạo phim mới
     * POST /api/movies
     */
    public function store(StoreMovieRequest $request)
    {
        try {
            // Lấy dữ liệu đã validate
            $data = $request->validated();

            // Xử lý upload Poster
            if ($request->hasFile('PosterUrl')) {
                $file = $request->file('PosterUrl');
                $hash = md5_file($file->getRealPath());
                $extension = $file->getClientOriginalExtension();
                $fileName = $hash . '.' . $extension;

                $folderPath = storage_path('app/public/uploads/movies');
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0775, true);
                }

                if (!file_exists($folderPath . '/' . $fileName)) {
                    $file->storeAs('uploads/movies', $fileName, 'public');
                }

                $data['PosterUrl'] = 'http://127.0.0.1:8000/storage/uploads/movies/' . $fileName;
            } else {
                $data['PosterUrl'] = null;
            }

            // Tính trạng thái phim dựa vào ngày phát hành
            $today = now()->startOfDay();
            $releaseDate = \Carbon\Carbon::parse($data['ReleaseDate'])->startOfDay();

            if ($releaseDate->gt($today)) {
                $data['Status'] = 'ComingSoon';
            } else {
                $data['Status'] = 'NowShowing';
            }

            // Tạo phim
            $result = $this->movieService->createWithValidation($data);
            
            if (!$result['success']) {
                return response()->json($result, 400);
            }

            $movie = $result['data'];

            // Kiểm tra suất chiếu
            if ($movie && method_exists($movie, 'showtimes')) {
                $hasShowtime = $movie->showtimes()
                    ->where('StartTime', '>=', now())
                    ->exists();
                    
                if (!$hasShowtime && $releaseDate->lt($today)) {
                    $movie->Status = 'Ended';
                    $movie->save();
                }
            }

            return new MovieResource($movie);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo phim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hiển thị chi tiết một phim
     * GET /api/movies/{id}
     */
    public function show($movieId)
    {
        try {
            $result = $this->movieService->find($movieId);
            
            if (!$result['success']) {
                return response()->json($result, 404);
            }
            
            return new MovieResource($result['data']);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật phim
     * PUT/PATCH /api/movies/{id}
     */
    public function update(StoreMovieRequest $request, $movieId)
    {
        try {
            $data = $request->validated();

            // Tìm movie cần cập nhật
            $movieResult = $this->movieService->find($movieId);
            if (!$movieResult['success']) {
                return response()->json($movieResult, 404);
            }
            
            $movie = $movieResult['data'];

            // Xử lý upload Poster mới
            if ($request->hasFile('PosterUrl')) {
                $file = $request->file('PosterUrl');
                $hash = md5_file($file->getRealPath());
                $extension = $file->getClientOriginalExtension();
                $fileName = $hash . '.' . $extension;

                $folderPath = storage_path('app/public/uploads/movies');
                if (!file_exists($folderPath)) {
                    mkdir($folderPath, 0775, true);
                }

                if (!file_exists($folderPath . '/' . $fileName)) {
                    $file->storeAs('uploads/movies', $fileName, 'public');
                }

                $data['PosterUrl'] = 'http://127.0.0.1:8000/storage/uploads/movies/' . $fileName;
            } else {
                // Giữ nguyên ảnh cũ
                $data['PosterUrl'] = $movie->PosterUrl;
            }

            // Tính lại trạng thái
            $today = now()->startOfDay();
            $releaseDate = \Carbon\Carbon::parse($data['ReleaseDate'])->startOfDay();

            if ($releaseDate->gt($today)) {
                $data['Status'] = 'ComingSoon';
            } else {
                $data['Status'] = 'NowShowing';
            }

            // Cập nhật phim
            $result = $this->movieService->updateWithValidation($movieId, $data);
            
            if (!$result['success']) {
                return response()->json($result, 400);
            }

            $updatedMovie = $result['data'];

            // Kiểm tra suất chiếu
            if ($updatedMovie && method_exists($updatedMovie, 'showtimes')) {
                $hasShowtime = $updatedMovie->showtimes()
                    ->where('StartTime', '>=', now())
                    ->exists();
                    
                if (!$hasShowtime && $releaseDate->lt($today)) {
                    $updatedMovie->Status = 'Ended';
                    $updatedMovie->save();
                }
            }

            return new MovieResource($updatedMovie);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật phim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa phim
     * DELETE /api/movies/{id}
     */
    public function destroy($movieId)
    {
        try {
            $result = $this->movieService->delete($movieId);
            
            if (!$result['success']) {
                return response()->json($result, 404);
            }
            
            return response()->json($result, 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa phim',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== PHIM ĐANG CHIẾU ====================

    /**
     * Lấy danh sách phim đang chiếu
     * GET /api/movies/now-showing
     */
    public function getNowShowing(): JsonResponse
    {
        try {
            $result = $this->movieService->getNowShowingMovies();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy chi tiết phim đang chiếu
     * GET /api/movies/{id}/detail
     */
    public function getDetail($movieId): JsonResponse
    {
        try {
            $result = $this->movieService->getMovieDetail($movieId);
            $statusCode = $result['success'] ? 200 : 404;
            return response()->json($result, $statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== PHIM SẮP CHIẾU ====================

    /**
     * Lấy danh sách phim sắp chiếu
     * GET /api/movies/coming-soon
     */
    public function getComingSoon(): JsonResponse
    {
        try {
            $result = $this->movieService->getComingSoonMovies();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== LỌC & TÌM KIẾM ====================

    /**
     * Lấy danh sách filters (thể loại, đạo diễn, diễn viên)
     * GET /api/movies/now-showing/filters
     */
    public function getNowShowingFilters(): JsonResponse
    {
        try {
            $result = $this->movieService->getNowShowingFilters();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lọc phim đang chiếu
     * POST /api/movies/now-showing/filter
     * Body: { "genre_id": 1, "director_name": "James", "actor_name": "Jason" }
     */
    public function filterNowShowing(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'genre_id',
                'director_name',
                'actor_name',
                'language',
                'rated'
            ]);
            
            $result = $this->movieService->filterNowShowing($filters);
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy phim đang chiếu theo thể loại
     * GET /api/movies/now-showing/genre/{genreId}
     */
    public function getNowShowingByGenre($genreId): JsonResponse
    {
        try {
            $result = $this->movieService->getNowShowingByGenre($genreId);
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tìm kiếm phim đang chiếu
     * GET /api/movies/now-showing/search?keyword=avatar
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
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== PHIM HOT & MỚI NHẤT ====================

    /**
     * Lấy phim hot (phổ biến)
     * GET /api/movies/hot?limit=10
     */
    public function getHotMovies(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $result = $this->movieService->getHotMovies($limit);
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy phim mới nhất
     * GET /api/movies/latest?limit=10
     */
    public function getLatestMovies(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);
            $result = $this->movieService->getLatestMovies($limit);
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // ==================== THỐNG KÊ ====================

    /**
     * Thống kê số lượng phim theo trạng thái
     * GET /api/movies/statistics
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $result = $this->movieService->getStatistics();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy phim theo trạng thái
     * GET /api/movies/by-status/{status}
     * status: NowShowing, ComingSoon, Ended
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
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
