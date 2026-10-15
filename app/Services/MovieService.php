<?php
namespace App\Services;

use App\Models\Movie;
use App\Repositories\MovieRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MovieService
{
    protected $movieRepository;

    public function __construct(MovieRepository $movieRepository)
    {
        $this->movieRepository = $movieRepository;
    }

    /**
     * ✅ Helper: Cập nhật trạng thái cho movie an toàn
     * 
     * @param mixed $movie
     * @return bool
     */
    private function safeUpdateStatus($movie): bool
    {
        // Kiểm tra null
        if (!$movie) {
            return false;
        }
        
        // Kiểm tra là object
        if (!is_object($movie)) {
            return false;
        }
        
        // Kiểm tra là instance của Movie Model
        if (!$movie instanceof \App\Models\Movie) {
            return false;
        }
        
        // Kiểm tra có method updateStatus
        if (!method_exists($movie, 'updateStatus')) {
            return false;
        }
        
        try {
            return $movie->updateStatus();
        } catch (\Exception $e) {
            Log::warning('Failed to update status for movie ID ' . ($movie->MovieId ?? 'unknown') . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ✅ Helper: Lấy movie và kiểm tra tồn tại
     * 
     * @param int $movieId
     * @return array ['success' => bool, 'movie' => Movie|null, 'message' => string]
     */
    private function findMovieOrFail(int $movieId): array
    {
        try {
            $movie = $this->movieRepository->find($movieId);
            
            if (!$movie) {
                return [
                    'success' => false,
                    'movie' => null,
                    'message' => 'Không tìm thấy phim với ID: ' . $movieId
                ];
            }
            
            return [
                'success' => true,
                'movie' => $movie,
                'message' => 'Tìm thấy phim'
            ];
        } catch (\Exception $e) {
            Log::error('Error finding movie: ' . $e->getMessage());
            return [
                'success' => false,
                'movie' => null,
                'message' => 'Có lỗi xảy ra khi tìm phim: ' . $e->getMessage()
            ];
        }
    }

    /**
     * ✅ Tự động cập nhật trạng thái cho tất cả phim
     */
    public function autoUpdateStatus(): array
    {
        try {
            return Movie::autoUpdateAllStatus();
        } catch (\Exception $e) {
            Log::error('Lỗi autoUpdateStatus: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi cập nhật trạng thái phim: ' . $e->getMessage(),
                'updated_count' => 0,
                'updates' => []
            ];
        }
    }

    /**
     * ✅ Cập nhật trạng thái cho một phim cụ thể
     */
    public function updateSingleStatus(int $movieId): array
    {
        try {
            $result = $this->findMovieOrFail($movieId);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => $result['message']
                ];
            }
            
            $movie = $result['movie'];
            $oldStatus = $movie->Status;
            
            // Cập nhật trạng thái
            $changed = $this->safeUpdateStatus($movie);
            
            return [
                'success' => true,
                'data' => $movie,
                'changed' => $changed,
                'old_status' => $oldStatus,
                'new_status' => $movie->Status,
                'message' => $changed ? 'Đã cập nhật trạng thái' : 'Trạng thái không thay đổi'
            ];
        } catch (\Exception $e) {
            Log::error('Lỗi updateSingleStatus: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Lấy tất cả phim
     */
    public function getAll()
    {
        try {
            // ✅ Tự động cập nhật trạng thái trước khi lấy
            $this->autoUpdateStatus();
            
            $movies = $this->movieRepository->all();
            
            return [
                'success' => true,
                'data' => $movies,
                'total' => $movies->count(),
                'message' => 'Lấy danh sách phim thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting all movies: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => [],
                'message' => 'Có lỗi xảy ra khi lấy danh sách phim',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Tìm phim theo ID
     */
    public function find($movieId)
    {
        try {
            $result = $this->findMovieOrFail($movieId);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => $result['message']
                ];
            }
            
            $movie = $result['movie'];
            
            // ✅ Cập nhật trạng thái an toàn
            $this->safeUpdateStatus($movie);

            return [
                'success' => true,
                'data' => $movie,
                'message' => 'Lấy thông tin phim thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error finding movie: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => null,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Tạo phim mới
     */
    public function create(array $data)
    {
        try {
            // Thêm thông tin người tạo
            if (Auth::check()) {
                $data['CreatedBy'] = Auth::user()->UserId;
            }
            
            // ✅ Tính trạng thái dựa trên ngày chiếu
            if (isset($data['ReleaseDate'])) {
                $releaseDate = Carbon::parse($data['ReleaseDate'])->startOfDay();
                $today = Carbon::now()->startOfDay();
                
                // Nếu ngày chiếu trong tương lai -> Sắp chiếu
                // Nếu ngày chiếu đã qua hoặc hôm nay -> Đang chiếu
                $data['Status'] = $releaseDate->gt($today) ? 'ComingSoon' : 'NowShowing';
            }
            
            $movie = $this->movieRepository->create($data);
            
            // ✅ Cập nhật trạng thái an toàn
            if ($movie) {
                $this->safeUpdateStatus($movie);
            }
            
            return [
                'success' => true,
                'data' => $movie,
                'message' => 'Tạo phim mới thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error creating movie: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => null,
                'message' => 'Có lỗi xảy ra khi tạo phim: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Cập nhật phim
     */
    public function update($movieId, array $data)
    {
        try {
            // Thêm thông tin người cập nhật
            if (Auth::check()) {
                $data['UpdatedBy'] = Auth::user()->UserId;
            }
            
            $result = $this->findMovieOrFail($movieId);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => $result['message']
                ];
            }
            
            $movie = $result['movie'];
            
            // ✅ Nếu cập nhật ngày chiếu, tính lại trạng thái
            if (isset($data['ReleaseDate'])) {
                $releaseDate = Carbon::parse($data['ReleaseDate'])->startOfDay();
                $today = Carbon::now()->startOfDay();
                
                // Kiểm tra suất chiếu trong tương lai
                $hasShowtime = false;
                if (method_exists($movie, 'showtimes')) {
                    $hasShowtime = $movie->showtimes()
                        ->where('StartTime', '>=', Carbon::now())
                        ->exists();
                }
                    
                if ($releaseDate->gt($today)) {
                    // Ngày chiếu trong tương lai -> Sắp chiếu
                    $data['Status'] = 'ComingSoon';
                } elseif ($hasShowtime) {
                    // Có suất chiếu -> Đang chiếu
                    $data['Status'] = 'NowShowing';
                } else {
                    // Không có suất chiếu, kiểm tra thời gian
                    $daysSinceRelease = $today->diffInDays($releaseDate);
                    $data['Status'] = $daysSinceRelease >= 60 ? 'Ended' : 'NowShowing';
                }
            }
            
            $updatedMovie = $this->movieRepository->update($movie, $data);
            
            // ✅ Cập nhật trạng thái an toàn
            if ($updatedMovie) {
                $this->safeUpdateStatus($updatedMovie);
            }
            
            return [
                'success' => true,
                'data' => $updatedMovie,
                'message' => 'Cập nhật phim thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error updating movie: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => null,
                'message' => 'Có lỗi xảy ra khi cập nhật phim: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Xóa phim
     */
    public function delete($movieId)
    {
        try {
            $result = $this->findMovieOrFail($movieId);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => $result['message']
                ];
            }
            
            $movie = $result['movie'];
            $this->movieRepository->delete($movie);
            
            return [
                'success' => true,
                'message' => 'Xóa phim thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error deleting movie: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa phim: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy danh sách phim đang chiếu với đầy đủ thông tin
     */
    public function getNowShowingMovies()
    {
        try {
            // ✅ Tự động cập nhật trạng thái trước khi lấy
            $this->autoUpdateStatus();
            
            $movies = $this->movieRepository->getNowShowingWithDetails();
            
            return [
                'success' => true,
                'data' => $movies,
                'message' => 'Lấy danh sách phim đang chiếu thành công',
                'total' => $movies->count()
            ];
        } catch (\Exception $e) {
            Log::error('Error getting now showing movies: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => [],
                'message' => 'Có lỗi xảy ra khi lấy danh sách phim đang chiếu',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy chi tiết phim theo ID
     */
    public function getMovieDetail($movieId)
    {
        try {
            $result = $this->findMovieOrFail($movieId);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => $result['message']
                ];
            }
            
            $movie = $result['movie'];
            
            // ✅ Cập nhật trạng thái an toàn
            $this->safeUpdateStatus($movie);

            return [
                'success' => true,
                'data' => $movie,
                'message' => 'Lấy chi tiết phim thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting movie detail: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => null,
                'message' => 'Có lỗi xảy ra khi lấy chi tiết phim: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy phim đang chiếu theo thể loại
     */
    public function getNowShowingByGenre($genreId)
    {
        try {
            $movies = $this->movieRepository->getNowShowingByGenre($genreId);
            
            return [
                'success' => true,
                'data' => $movies,
                'message' => 'Lấy danh sách phim theo thể loại thành công',
                'total' => $movies->count()
            ];
        } catch (\Exception $e) {
            Log::error('Error getting movies by genre: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => [],
                'message' => 'Có lỗi xảy ra khi lấy phim theo thể loại: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Tìm kiếm phim đang chiếu
     */
    public function searchNowShowing($keyword)
    {
        try {
            if (empty($keyword)) {
                return [
                    'success' => false,
                    'data' => [],
                    'message' => 'Vui lòng nhập từ khóa tìm kiếm'
                ];
            }

            $movies = $this->movieRepository->searchNowShowing($keyword);
            
            return [
                'success' => true,
                'data' => $movies,
                'message' => 'Tìm kiếm phim thành công',
                'total' => $movies->count(),
                'keyword' => $keyword
            ];
        } catch (\Exception $e) {
            Log::error('Error searching movies: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => [],
                'message' => 'Có lỗi xảy ra khi tìm kiếm: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy phim sắp chiếu
     */
    public function getComingSoonMovies()
    {
        try {
            // ✅ Tự động cập nhật trạng thái trước khi lấy
            $this->autoUpdateStatus();
            
            $movies = $this->movieRepository->getComingSoon();
            
            return [
                'success' => true,
                'data' => $movies,
                'message' => 'Lấy danh sách phim sắp chiếu thành công',
                'total' => $movies->count()
            ];
        } catch (\Exception $e) {
            Log::error('Error getting coming soon movies: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => [],
                'message' => 'Có lỗi xảy ra khi lấy danh sách phim sắp chiếu: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy danh sách thể loại, đạo diễn, diễn viên từ phim đang chiếu
     */
    public function getNowShowingFilters()
    {
        try {
            $filters = $this->movieRepository->getNowShowingFilters();
            
            return [
                'success' => true,
                'data' => $filters,
                'message' => 'Lấy danh sách filters thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting filters: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => [
                    'genres' => [],
                    'directors' => [],
                    'actors' => [],
                    'total_movies' => 0
                ],
                'message' => 'Có lỗi xảy ra khi lấy filters: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lọc phim đang chiếu với nhiều điều kiện
     */
    public function filterNowShowing(array $filters)
    {
        try {
            $movies = $this->movieRepository->filterNowShowing($filters);
            
            return [
                'success' => true,
                'data' => $movies,
                'total' => $movies->count(),
                'filters_applied' => $filters,
                'message' => 'Lọc phim thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error filtering movies: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => [],
                'message' => 'Có lỗi xảy ra khi lọc phim: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy phim theo trạng thái
     */
    public function getByStatus($status)
    {
        try {
            // ✅ Tự động cập nhật trạng thái trước khi lấy
            $this->autoUpdateStatus();
            
            $movies = $this->movieRepository->getByStatus($status);
            
            return [
                'success' => true,
                'data' => $movies,
                'total' => $movies->count(),
                'status' => $status,
                'message' => 'Lấy danh sách phim theo trạng thái thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting movies by status: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => [],
                'message' => 'Có lỗi xảy ra khi lấy phim theo trạng thái: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy phim mới nhất
     */
    public function getLatestMovies($limit = 10)
    {
        try {
            $movies = $this->movieRepository->getLatestMovies($limit);
            
            return [
                'success' => true,
                'data' => $movies,
                'total' => $movies->count(),
                'message' => 'Lấy danh sách phim mới nhất thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting latest movies: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => [],
                'message' => 'Có lỗi xảy ra khi lấy phim mới nhất: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy phim hot (phổ biến)
     */
    public function getHotMovies($limit = 10)
    {
        try {
            $movies = $this->movieRepository->getHotMovies($limit);
            
            return [
                'success' => true,
                'data' => $movies,
                'total' => $movies->count(),
                'message' => 'Lấy danh sách phim hot thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting hot movies: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => [],
                'message' => 'Có lỗi xảy ra khi lấy phim hot: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Thống kê số lượng phim theo trạng thái
     */
    public function getStatistics()
    {
        try {
            // ✅ Tự động cập nhật trạng thái trước khi thống kê
            $this->autoUpdateStatus();
            
            $stats = $this->movieRepository->countByStatus();
            
            return [
                'success' => true,
                'data' => $stats,
                'message' => 'Lấy thống kê thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting statistics: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => null,
                'message' => 'Có lỗi xảy ra khi lấy thống kê: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * ✅ Kiểm tra và cập nhật trạng thái cho một phim (method helper)
     * 
     * @param mixed $movie
     * @return bool
     */
    public function updateMovieStatus($movie): bool
    {
        return $this->safeUpdateStatus($movie);
    }

    /**
     * ✅ Kiểm tra và cập nhật trạng thái cho nhiều phim (method helper)
     * 
     * @param array $movieIds
     * @return array
     */
    public function updateMultipleStatus(array $movieIds): array
    {
        $results = [];
        foreach ($movieIds as $movieId) {
            $results[$movieId] = $this->updateSingleStatus($movieId);
        }
        return $results;
    }

    /**
     * Validate dữ liệu phim trước khi tạo/cập nhật
     */
    private function validateMovieData(array $data): array
    {
        $errors = [];

        // Title
        if (empty($data['Title'])) {
            $errors['Title'] = 'Tiêu đề phim không được để trống';
        }

        // Duration
        if (isset($data['Duration'])) {
            if (!is_numeric($data['Duration']) || $data['Duration'] < 0) {
                $errors['Duration'] = 'Thời lượng phim không hợp lệ (phải là số dương)';
            }
        }

        // Status
        $validStatuses = ['NowShowing', 'ComingSoon', 'Ended'];
        if (isset($data['Status']) && !in_array($data['Status'], $validStatuses)) {
            $errors['Status'] = 'Trạng thái phim không hợp lệ. Chấp nhận: ' . implode(', ', $validStatuses);
        }

        // ReleaseDate
        if (isset($data['ReleaseDate'])) {
            try {
                Carbon::parse($data['ReleaseDate']);
            } catch (\Exception $e) {
                $errors['ReleaseDate'] = 'Ngày phát hành không hợp lệ';
            }
        }

        return $errors;
    }

    /**
     * Tạo phim với validation
     */
    public function createWithValidation(array $data): array
    {
        $errors = $this->validateMovieData($data);
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $errors
            ];
        }

        return $this->create($data);
    }

    /**
     * Cập nhật phim với validation
     */
    public function updateWithValidation($movieId, array $data): array
    {
        $errors = $this->validateMovieData($data);
        
        if (!empty($errors)) {
            return [
                'success' => false,
                'data' => null,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $errors
            ];
        }

        return $this->update($movieId, $data);
    }
}