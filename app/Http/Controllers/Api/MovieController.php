<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMovieRequest;
use App\Http\Resources\MovieResource;
use App\Services\MovieService;
use App\Models\Movie;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MovieController extends Controller
{
    protected $movieService;

    public function __construct(MovieService $movieService)
    {
        $this->movieService = $movieService;

        $this->middleware(['auth:api', 'checkrole:Admin'])
            ->only(['store', 'update', 'destroy']);
    }

    // ==================== CRUD CƠ BẢN ====================

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

    // ✅ Phân trang server-side
    // GET /api/movies/paged?page=1&limit=10&search=avatar&status=NowShowing
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

    public function store(StoreMovieRequest $request)
    {
        try {
            $data = $request->validated();

            if ($request->hasFile('PosterUrl')) {
                $file      = $request->file('PosterUrl');
                $hash      = md5_file($file->getRealPath());
                $extension = $file->getClientOriginalExtension();
                $fileName  = $hash . '.' . $extension;

                $folderPath = storage_path('app/public/uploads/movies');
                if (!file_exists($folderPath)) mkdir($folderPath, 0775, true);
                if (!file_exists($folderPath . '/' . $fileName)) {
                    $file->storeAs('uploads/movies', $fileName, 'public');
                }
                $data['PosterUrl'] = 'http://127.0.0.1:8000/storage/uploads/movies/' . $fileName;
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

    public function update(StoreMovieRequest $request, $movieId)
    {
        try {
            $data        = $request->validated();
            $movieResult = $this->movieService->find($movieId);
            if (!$movieResult['success']) return response()->json($movieResult, 404);
            $movie = $movieResult['data'];

            if ($request->hasFile('PosterUrl')) {
                $file      = $request->file('PosterUrl');
                $hash      = md5_file($file->getRealPath());
                $extension = $file->getClientOriginalExtension();
                $fileName  = $hash . '.' . $extension;

                $folderPath = storage_path('app/public/uploads/movies');
                if (!file_exists($folderPath)) mkdir($folderPath, 0775, true);
                if (!file_exists($folderPath . '/' . $fileName)) {
                    $file->storeAs('uploads/movies', $fileName, 'public');
                }
                $data['PosterUrl'] = 'http://127.0.0.1:8000/storage/uploads/movies/' . $fileName;
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

    public function destroy($movieId)
    {
        try {
            $result = $this->movieService->delete($movieId);
            if (!$result['success']) return response()->json($result, 404);
            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra khi xóa phim', 'error' => $e->getMessage()], 500);
        }
    }

    // ==================== PHIM ĐANG CHIẾU ====================

    public function getNowShowing(): JsonResponse
    {
        try {
            $result = $this->movieService->getNowShowingMovies();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

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

    public function getNowShowingFilters(): JsonResponse
    {
        try {
            $result = $this->movieService->getNowShowingFilters();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

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

    public function getNowShowingByGenre($genreId): JsonResponse
    {
        try {
            $result = $this->movieService->getNowShowingByGenre($genreId);
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

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

    public function getHotMovies(Request $request): JsonResponse
    {
        try {
            $result = $this->movieService->getHotMovies($request->input('limit', 10));
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

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

    public function getStatistics(): JsonResponse
    {
        try {
            $result = $this->movieService->getStatistics();
            return response()->json($result, $result['success'] ? 200 : 500);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Có lỗi xảy ra', 'error' => $e->getMessage()], 500);
        }
    }

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
}