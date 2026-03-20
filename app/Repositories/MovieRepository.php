<?php
namespace App\Repositories;

use App\Models\Movie;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class MovieRepository
{
    protected $movie;

    public function __construct(Movie $movie)
    {
        $this->movie = $movie;
    }

    /**
     * Lấy tất cả phim
     */
    public function all()
    {
        return $this->movie->with(['genre', 'directors', 'actors'])->get();
    }

    /**
     * Tìm phim theo ID
     */
    public function find($movieId)
    {
        return $this->movie->with(['genre', 'directors', 'actors'])->find($movieId);
    }

    /**
     * Tạo phim mới
     */
    public function create(array $data)
    {
        return $this->movie->create($data);
    }

    /**
     * Cập nhật phim
     */
    public function update(Movie $movie, array $data)
    {
        $movie->update($data);
        return $movie->fresh(['genre', 'directors', 'actors']);
    }

    /**
     * Xóa phim
     */
    public function delete(Movie $movie)
    {
        return $movie->delete();
    }

    /**
     * Lấy tất cả phim đang chiếu với thông tin đầy đủ
     * Bao gồm: Diễn viên, Đạo diễn, Thể loại
     */
    public function getNowShowingWithDetails()
    {
        return $this->movie
            ->where('Status', 'NowShowing')
            ->with([
                'genre',           // Thể loại
                'directors',       // Đạo diễn
                'actors'          // Diễn viên
            ])
            ->orderBy('ReleaseDate', 'desc')
            ->get()
            ->map(function($movie) {
                return [
                    'MovieId' => $movie->MovieId,
                    'Title' => $movie->Title,
                    'Slug' => $movie->Slug,
                    'Description' => $movie->Description,
                    'Duration' => $movie->Duration,
                    'ReleaseDate' => $movie->ReleaseDate?->format('Y-m-d'),
                    'PosterUrl' => $movie->PosterUrl,
                    'TrailerUrl' => $movie->TrailerUrl,
                    'Language' => $movie->Language,
                    'Rated' => $movie->Rated,
                    'Status' => $movie->Status,
                    
                    // Thông tin Thể loại
                    'genre' => $movie->genre ? [
                        'GenreId' => $movie->genre->GenreId,
                        'Name' => $movie->genre->Name,
                        'Description' => $movie->genre->Description
                    ] : null,
                    
                    // Thông tin Đạo diễn
                    'directors' => $movie->directors->map(function($director) {
                        return [
                            'CastId' => $director->CastId,
                            'Name' => $director->Name,
                            'Role' => $director->Role
                        ];
                    }),
                    
                    // Thông tin Diễn viên
                    'actors' => $movie->actors->map(function($actor) {
                        return [
                            'CastId' => $actor->CastId,
                            'Name' => $actor->Name,
                            'Role' => $actor->Role
                        ];
                    })
                ];
            });
    }

    /**
     * Lấy chi tiết một phim theo ID với đầy đủ thông tin
     */
    public function getMovieDetailById($movieId)
    {
        $movie = $this->movie
            ->where('MovieId', $movieId)
            ->with(['genre', 'directors', 'actors'])
            ->first();

        if (!$movie) {
            return null;
        }

        return [
            'MovieId' => $movie->MovieId,
            'Title' => $movie->Title,
            'Slug' => $movie->Slug,
            'Description' => $movie->Description,
            'Duration' => $movie->Duration,
            'ReleaseDate' => $movie->ReleaseDate?->format('Y-m-d'),
            'PosterUrl' => $movie->PosterUrl,
            'TrailerUrl' => $movie->TrailerUrl,
            'Language' => $movie->Language,
            'Rated' => $movie->Rated,
            'Status' => $movie->Status,
            
            'genre' => $movie->genre ? [
                'GenreId' => $movie->genre->GenreId,
                'Name' => $movie->genre->Name
            ] : null,
            
            'directors' => $movie->directors->map(fn($d) => [
                'CastId' => $d->CastId,
                'Name' => $d->Name
            ]),
            
            'actors' => $movie->actors->map(fn($a) => [
                'CastId' => $a->CastId,
                'Name' => $a->Name
            ])
        ];
    }

    /**
     * Lấy phim đang chiếu theo thể loại
     */
    public function getNowShowingByGenre($genreId)
    {
        return $this->movie
            ->where('Status', 'NowShowing')
            ->where('GenreId', $genreId)
            ->with(['genre', 'directors', 'actors'])
            ->orderBy('ReleaseDate', 'desc')
            ->get();
    }

    /**
     * Tìm kiếm phim đang chiếu theo tên
     */
    public function searchNowShowing($keyword)
    {
        return $this->movie
            ->where('Status', 'NowShowing')
            ->where(function($query) use ($keyword) {
                $query->where('Title', 'LIKE', "%{$keyword}%")
                      ->orWhere('Description', 'LIKE', "%{$keyword}%");
            })
            ->with(['genre', 'directors', 'actors'])
            ->orderBy('ReleaseDate', 'desc')
            ->get();
    }

    /**
     * Lấy phim sắp chiếu
     */
    public function getComingSoon()
    {
        return $this->movie
            ->where('Status', 'ComingSoon')
            ->with(['genre', 'directors', 'actors'])
            ->orderBy('ReleaseDate', 'asc')
            ->get();
    }

    /**
     * Lấy danh sách thể loại, đạo diễn, diễn viên từ phim đang chiếu
     * (Phiên bản mới - sử dụng relationships)
     */
    public function getNowShowingFilters()
    {
        // Lấy tất cả phim đang chiếu với relationships
        $nowShowingMovies = $this->movie
            ->where('Status', 'NowShowing')
            ->with(['genre', 'directors', 'actors'])
            ->get();

        // Lấy danh sách thể loại (unique)
        $genres = $nowShowingMovies
            ->pluck('genre')
            ->filter()  // Loại bỏ null
            ->unique('GenreId')
            ->sortBy('Name')
            ->values()
            ->map(function($genre) {
                return [
                    'GenreId' => $genre->GenreId,
                    'Name' => $genre->Name
                ];
            });

        // Lấy danh sách đạo diễn (unique)
        $directors = $nowShowingMovies
            ->pluck('directors')
            ->flatten()
            ->unique('CastId')
            ->sortBy('Name')
            ->values()
            ->map(function($director) {
                return [
                    'CastId' => $director->CastId,
                    'Name' => $director->Name
                ];
            });

        // Lấy danh sách diễn viên (unique)
        $actors = $nowShowingMovies
            ->pluck('actors')
            ->flatten()
            ->unique('CastId')
            ->sortBy('Name')
            ->values()
            ->map(function($actor) {
                return [
                    'CastId' => $actor->CastId,
                    'Name' => $actor->Name
                ];
            });

        return [
            'genres' => $genres,
            'directors' => $directors,
            'actors' => $actors,
            'total_movies' => $nowShowingMovies->count()
        ];
    }

    /**
     * Lọc phim đang chiếu với nhiều điều kiện
     * 
     * @param array $filters ['genre_id' => int, 'director_name' => string, 'actor_name' => string]
     */
    public function filterNowShowing(array $filters)
    {
        $query = $this->movie
            ->where('Status', 'NowShowing')
            ->with(['genre', 'directors', 'actors']);

        // Lọc theo thể loại
        if (!empty($filters['genre_id'])) {
            $query->where('GenreId', $filters['genre_id']);
        }

        // Lọc theo đạo diễn
        if (!empty($filters['director_name'])) {
            $query->whereHas('directors', function($q) use ($filters) {
                $q->where('Name', 'LIKE', "%{$filters['director_name']}%");
            });
        }

        // Lọc theo diễn viên
        if (!empty($filters['actor_name'])) {
            $query->whereHas('actors', function($q) use ($filters) {
                $q->where('Name', 'LIKE', "%{$filters['actor_name']}%");
            });
        }

        // Lọc theo ngôn ngữ
        if (!empty($filters['language'])) {
            $query->where('Language', 'LIKE', "%{$filters['language']}%");
        }

        // Lọc theo độ tuổi
        if (!empty($filters['rated'])) {
            $query->where('Rated', $filters['rated']);
        }

        return $query->orderBy('ReleaseDate', 'desc')->get();
    }

    /**
     * Lấy phim theo trạng thái
     */
    public function getByStatus($status)
    {
        return $this->movie
            ->where('Status', $status)
            ->with(['genre', 'directors', 'actors'])
            ->orderBy('ReleaseDate', 'desc')
            ->get();
    }

    /**
     * Lấy phim mới nhất (theo ngày thêm vào)
     */
    public function getLatestMovies($limit = 10)
    {
        return $this->movie
            ->with(['genre', 'directors', 'actors'])
            ->orderBy('CreatedAt', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy phim hot (có nhiều lượt xem/đánh giá)
     * Tùy chỉnh logic theo nhu cầu
     */
    public function getHotMovies($limit = 10)
    {
        return $this->movie
            ->where('Status', 'NowShowing')
            ->withCount('reviews')  // Đếm số review
            ->with(['genre', 'directors', 'actors'])
            ->orderBy('reviews_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Lấy phim theo nhiều IDs
     */
    public function getByIds(array $movieIds)
    {
        return $this->movie
            ->whereIn('MovieId', $movieIds)
            ->with(['genre', 'directors', 'actors'])
            ->get();
    }

    /**
     * Thống kê số lượng phim theo trạng thái
     */
    public function countByStatus()
    {
        return [
            'now_showing' => $this->movie->where('Status', 'NowShowing')->count(),
            'coming_soon' => $this->movie->where('Status', 'ComingSoon')->count(),
            'ended' => $this->movie->where('Status', 'Ended')->count(),
            'total' => $this->movie->count()
        ];
    }
}