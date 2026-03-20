<?php
namespace App\Services;

use App\Repositories\MovieRepository;
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
     * Lấy tất cả phim
     */
    public function getAll()
    {
        try {
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
            $movie = $this->movieRepository->find($movieId);
            
            if (!$movie) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Không tìm thấy phim'
                ];
            }

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
                'message' => 'Có lỗi xảy ra',
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
            
            $movie = $this->movieRepository->create($data);
            
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
                'message' => 'Có lỗi xảy ra khi tạo phim',
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
            
            $movie = $this->movieRepository->find($movieId);
            
            if (!$movie) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Không tìm thấy phim'
                ];
            }
            
            $updatedMovie = $this->movieRepository->update($movie, $data);
            
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
                'message' => 'Có lỗi xảy ra khi cập nhật phim',
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
            $movie = $this->movieRepository->find($movieId);
            
            if (!$movie) {
                return [
                    'success' => false,
                    'message' => 'Không tìm thấy phim'
                ];
            }
            
            $this->movieRepository->delete($movie);
            
            return [
                'success' => true,
                'message' => 'Xóa phim thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error deleting movie: ' . $e->getMessage());
            
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa phim',
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
                'message' => 'Có lỗi xảy ra khi lấy danh sách phim',
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
            $movie = $this->movieRepository->getMovieDetailById($movieId);
            
            if (!$movie) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => 'Không tìm thấy phim'
                ];
            }

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
                'message' => 'Có lỗi xảy ra khi lấy chi tiết phim',
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
                'message' => 'Có lỗi xảy ra',
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
                'message' => 'Có lỗi xảy ra khi tìm kiếm',
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
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Lấy danh sách thể loại, đạo diễn, diễn viên từ phim đang chiếu
     * (Để làm filters)
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
                'message' => 'Có lỗi xảy ra',
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
                'message' => 'Có lỗi xảy ra khi lọc phim',
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
            $movies = $this->movieRepository->getByStatus($status);
            
            return [
                'success' => true,
                'data' => $movies,
                'total' => $movies->count(),
                'status' => $status,
                'message' => 'Lấy danh sách phim thành công'
            ];
        } catch (\Exception $e) {
            Log::error('Error getting movies by status: ' . $e->getMessage());
            
            return [
                'success' => false,
                'data' => [],
                'message' => 'Có lỗi xảy ra',
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
                'message' => 'Có lỗi xảy ra',
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
                'message' => 'Có lỗi xảy ra',
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
                'message' => 'Có lỗi xảy ra',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Validate dữ liệu phim trước khi tạo/cập nhật
     */
    private function validateMovieData(array $data)
    {
        $errors = [];

        // Validate Title
        if (empty($data['Title'])) {
            $errors[] = 'Tiêu đề phim không được để trống';
        }

        // Validate Duration
        if (isset($data['Duration']) && $data['Duration'] < 0) {
            $errors[] = 'Thời lượng phim không hợp lệ';
        }

        // Validate Status
        $validStatuses = ['NowShowing', 'ComingSoon', 'Ended'];
        if (isset($data['Status']) && !in_array($data['Status'], $validStatuses)) {
            $errors[] = 'Trạng thái phim không hợp lệ';
        }

        return $errors;
    }

    /**
     * Tạo phim với validation
     */
    public function createWithValidation(array $data)
    {
        // Validate dữ liệu
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
    public function updateWithValidation($movieId, array $data)
    {
        // Validate dữ liệu
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