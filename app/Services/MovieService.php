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
     * ✅ Lấy phim có phân trang (Gọi từ Controller)
     */
    public function getPaged($page, $limit, $search = '', $status = '')
    {
        try {
            return $this->movieRepository->getPaged($page, $limit, $search, $status);
        } catch (\Exception $e) {
            Log::error('Error getting paged movies: ' . $e->getMessage());
            throw $e;
        }
    }

    private function findMovieOrFail(int $movieId): array
    {
        try {
            $movie = $this->movieRepository->find($movieId);
            
            if (!$movie) {
                return ['success' => false, 'movie' => null, 'message' => 'Không tìm thấy phim với ID: ' . $movieId];
            }
            
            return ['success' => true, 'movie' => $movie, 'message' => 'Tìm thấy phim'];
        } catch (\Exception $e) {
            Log::error('Error finding movie: ' . $e->getMessage());
            return ['success' => false, 'movie' => null, 'message' => 'Có lỗi xảy ra khi tìm phim: ' . $e->getMessage()];
        }
    }

    // ✅ ĐÃ XÓA HOÀN TOÀN HÀM autoUpdateStatus()
    // ✅ ĐÃ XÓA HOÀN TOÀN HÀM updateSingleStatus()
    // ✅ ĐÃ XÓA HOÀN TOÀN HÀM safeUpdateStatus()

    public function getAll()
    {
        try {
            // ✅ ĐÃ XÓA LỆNH TỰ ĐỘNG CẬP NHẬT: $this->autoUpdateStatus();
            $movies = $this->movieRepository->all();
            return [
                'success' => true,
                'data' => $movies,
                'total' => $movies->count(),
                'message' => 'Lấy danh sách phim thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting all movies: ' . $e->getMessage());
            return ['success' => false, 'data' => [], 'message' => 'Có lỗi xảy ra khi lấy danh sách phim', 'error' => $e->getMessage()];
        }
    }

    public function find($movieId)
    {
        try {
            $result = $this->findMovieOrFail($movieId);
            if (!$result['success']) return ['success' => false, 'data' => null, 'message' => $result['message']];
            
            $movie = $result['movie'];
            // ✅ ĐÃ XÓA: $this->safeUpdateStatus($movie);
            
            return ['success' => true, 'data' => $movie, 'message' => 'Lấy thông tin phim thành công'];
        } catch (\Exception $e) {
            Log::error('Error finding movie: ' . $e->getMessage());
            return ['success' => false, 'data' => null, 'message' => 'Có lỗi xảy ra: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    public function create(array $data)
    {
        try {
            if (Auth::check()) $data['CreatedBy'] = Auth::user()->UserId;
            
            // ✅ ĐÃ XÓA LOGIC TỰ ĐỘNG TÍNH TRẠNG THÁI DỰA VÀO NGÀY
            
            $movie = $this->movieRepository->create($data);
            // ✅ ĐÃ XÓA: if ($movie) $this->safeUpdateStatus($movie);
            
            return ['success' => true, 'data' => $movie, 'message' => 'Tạo phim mới thành công'];
        } catch (\Exception $e) {
            Log::error('Error creating movie: ' . $e->getMessage());
            return ['success' => false, 'data' => null, 'message' => 'Có lỗi xảy ra khi tạo phim: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    public function update($movieId, array $data)
    {
        try {
            if (Auth::check()) $data['UpdatedBy'] = Auth::user()->UserId;
            
            $result = $this->findMovieOrFail($movieId);
            if (!$result['success']) return ['success' => false, 'data' => null, 'message' => $result['message']];
            
            $movie = $result['movie'];
            
            // ✅ ĐÃ XÓA TOÀN BỘ LOGIC TỰ ĐỘNG TÍNH TRẠNG THÁI DỰA VÀO NGÀY
            
            $updatedMovie = $this->movieRepository->update($movie, $data);
            // ✅ ĐÃ XÓA: if ($updatedMovie) $this->safeUpdateStatus($updatedMovie);
            
            return ['success' => true, 'data' => $updatedMovie, 'message' => 'Cập nhật phim thành công'];
        } catch (\Exception $e) {
            Log::error('Error updating movie: ' . $e->getMessage());
            return ['success' => false, 'data' => null, 'message' => 'Có lỗi xảy ra khi cập nhật phim: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    public function delete($movieId)
    {
        try {
            $result = $this->findMovieOrFail($movieId);
            if (!$result['success']) return ['success' => false, 'message' => $result['message']];
            
            $movie = $result['movie'];
            $this->movieRepository->delete($movie);
            return ['success' => true, 'message' => 'Xóa phim thành công'];
        } catch (\Exception $e) {
            Log::error('Error deleting movie: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Có lỗi xảy ra khi xóa phim: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    public function getNowShowingMovies()
    {
        try {
            // ✅ ĐÃ XÓA LỆNH TỰ ĐỘNG CẬP NHẬT
            $movies = $this->movieRepository->getNowShowingWithDetails();
            return ['success' => true, 'data' => $movies, 'message' => 'Lấy danh sách phim đang chiếu thành công', 'total' => $movies->count()];
        } catch (\Exception $e) {
            Log::error('Error getting now showing movies: ' . $e->getMessage());
            return ['success' => false, 'data' => [], 'message' => 'Có lỗi xảy ra khi lấy danh sách phim đang chiếu', 'error' => $e->getMessage()];
        }
    }

    public function getMovieDetail($movieId)
    {
        try {
            $result = $this->findMovieOrFail($movieId);
            if (!$result['success']) return ['success' => false, 'data' => null, 'message' => $result['message']];
            
            $movie = $result['movie'];
            // ✅ ĐÃ XÓA: $this->safeUpdateStatus($movie);
            
            return ['success' => true, 'data' => $movie, 'message' => 'Lấy chi tiết phim thành công'];
        } catch (\Exception $e) {
            Log::error('Error getting movie detail: ' . $e->getMessage());
            return ['success' => false, 'data' => null, 'message' => 'Có lỗi xảy ra khi lấy chi tiết phim: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    public function getNowShowingByGenre($genreId)
    {
        try {
            $movies = $this->movieRepository->getNowShowingByGenre($genreId);
            return ['success' => true, 'data' => $movies, 'message' => 'Lấy danh sách phim theo thể loại thành công', 'total' => $movies->count()];
        } catch (\Exception $e) {
            Log::error('Error getting movies by genre: ' . $e->getMessage());
            return ['success' => false, 'data' => [], 'message' => 'Có lỗi xảy ra khi lấy phim theo thể loại: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    public function searchNowShowing($keyword)
    {
        try {
            if (empty($keyword)) return ['success' => false, 'data' => [], 'message' => 'Vui lòng nhập từ khóa tìm kiếm'];
            $movies = $this->movieRepository->searchNowShowing($keyword);
            return ['success' => true, 'data' => $movies, 'message' => 'Tìm kiếm phim thành công', 'total' => $movies->count(), 'keyword' => $keyword];
        } catch (\Exception $e) {
            Log::error('Error searching movies: ' . $e->getMessage());
            return ['success' => false, 'data' => [], 'message' => 'Có lỗi xảy ra khi tìm kiếm: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    public function getComingSoonMovies()
    {
        try {
            // ✅ ĐÃ XÓA LỆNH TỰ ĐỘNG CẬP NHẬT
            $movies = $this->movieRepository->getComingSoon();
            return ['success' => true, 'data' => $movies, 'message' => 'Lấy danh sách phim sắp chiếu thành công', 'total' => $movies->count()];
        } catch (\Exception $e) {
            Log::error('Error getting coming soon movies: ' . $e->getMessage());
            return ['success' => false, 'data' => [], 'message' => 'Có lỗi xảy ra khi lấy danh sách phim sắp chiếu: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    public function getNowShowingFilters()
    {
        try {
            $filters = $this->movieRepository->getNowShowingFilters();
            return ['success' => true, 'data' => $filters, 'message' => 'Lấy danh sách filters thành công'];
        } catch (\Exception $e) {
            Log::error('Error getting filters: ' . $e->getMessage());
            return ['success' => false, 'data' => ['genres' => [], 'directors' => [], 'actors' => [], 'total_movies' => 0], 'message' => 'Có lỗi xảy ra khi lấy filters: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    public function filterNowShowing(array $filters)
    {
        try {
            $movies = $this->movieRepository->filterNowShowing($filters);
            return ['success' => true, 'data' => $movies, 'total' => $movies->count(), 'filters_applied' => $filters, 'message' => 'Lọc phim thành công'];
        } catch (\Exception $e) {
            Log::error('Error filtering movies: ' . $e->getMessage());
            return ['success' => false, 'data' => [], 'message' => 'Có lỗi xảy ra khi lọc phim: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    public function getByStatus($status)
    {
        try {
            // ✅ ĐÃ XÓA LỆNH TỰ ĐỘNG CẬP NHẬT
            $movies = $this->movieRepository->getByStatus($status);
            return ['success' => true, 'data' => $movies, 'total' => $movies->count(), 'status' => $status, 'message' => 'Lấy danh sách phim theo trạng thái thành công'];
        } catch (\Exception $e) {
            Log::error('Error getting movies by status: ' . $e->getMessage());
            return ['success' => false, 'data' => [], 'message' => 'Có lỗi xảy ra khi lấy phim theo trạng thái: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    public function getLatestMovies($limit = 10)
    {
        try {
            $movies = $this->movieRepository->getLatestMovies($limit);
            return ['success' => true, 'data' => $movies, 'total' => $movies->count(), 'message' => 'Lấy danh sách phim mới nhất thành công'];
        } catch (\Exception $e) {
            Log::error('Error getting latest movies: ' . $e->getMessage());
            return ['success' => false, 'data' => [], 'message' => 'Có lỗi xảy ra khi lấy phim mới nhất: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    public function getHotMovies($limit = 10)
    {
        try {
            $movies = $this->movieRepository->getHotMovies($limit);
            return ['success' => true, 'data' => $movies, 'total' => $movies->count(), 'message' => 'Lấy danh sách phim hot thành công'];
        } catch (\Exception $e) {
            Log::error('Error getting hot movies: ' . $e->getMessage());
            return ['success' => false, 'data' => [], 'message' => 'Có lỗi xảy ra khi lấy phim hot: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    public function getStatistics()
    {
        try {
            // ✅ ĐÃ XÓA LỆNH TỰ ĐỘNG CẬP NHẬT
            $stats = $this->movieRepository->countByStatus();
            return ['success' => true, 'data' => $stats, 'message' => 'Lấy thống kê thành công'];
        } catch (\Exception $e) {
            Log::error('Error getting statistics: ' . $e->getMessage());
            return ['success' => false, 'data' => null, 'message' => 'Có lỗi xảy ra khi lấy thống kê: ' . $e->getMessage(), 'error' => $e->getMessage()];
        }
    }

    private function validateMovieData(array $data): array
    {
        $errors = [];
        if (empty($data['Title'])) $errors['Title'] = 'Tiêu đề phim không được để trống';
        if (isset($data['Duration']) && (!is_numeric($data['Duration']) || $data['Duration'] < 0)) $errors['Duration'] = 'Thời lượng phim không hợp lệ (phải là số dương)';
        
        $validStatuses = ['NowShowing', 'ComingSoon', 'Ended'];
        if (isset($data['Status']) && !in_array($data['Status'], $validStatuses)) $errors['Status'] = 'Trạng thái phim không hợp lệ. Chấp nhận: ' . implode(', ', $validStatuses);
        
        if (isset($data['ReleaseDate'])) {
            try { Carbon::parse($data['ReleaseDate']); } 
            catch (\Exception $e) { $errors['ReleaseDate'] = 'Ngày phát hành không hợp lệ'; }
        }
        return $errors;
    }

    public function createWithValidation(array $data): array
    {
        $errors = $this->validateMovieData($data);
        if (!empty($errors)) return ['success' => false, 'data' => null, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors];
        return $this->create($data);
    }

    public function updateWithValidation($movieId, array $data): array
    {
        $errors = $this->validateMovieData($data);
        if (!empty($errors)) return ['success' => false, 'data' => null, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $errors];
        return $this->update($movieId, $data);
    }
}