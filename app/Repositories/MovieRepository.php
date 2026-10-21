<?php

namespace App\Repositories;

use App\Models\Movie;
use App\Models\Genre;
use App\Models\Moviecast;
use Illuminate\Support\Facades\Cache;

class MovieRepository
{
    protected $movie;
    const CACHE_TIME = 300; // 5 phút

    public function __construct(Movie $movie)
    {
        $this->movie = $movie;
    }

    /**
     * ✅ Lấy phim có phân trang + Cache (Dùng cho Admin)
     */
    public function getPaged($page, $limit, $search = '', $status = '', $genre = '')
    {
        // ✅ Đã đổi tên cache thành v4 để chắc chắn không dính cache cũ của lần sửa trước
        $cacheKey = "movies_paged_v4_p{$page}_l{$limit}_" . md5($search . $status . $genre);

        return Cache::remember($cacheKey, self::CACHE_TIME, function () use ($page, $limit, $search, $status, $genre) {
            $query = $this->movie->with('castMembers');

            // 1. Lọc theo từ khóa tìm kiếm
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('Title', 'like', "%{$search}%")
                      ->orWhere('Description', 'like', "%{$search}%");
                });
            }

            // 2. Lọc theo trạng thái
            if ($status) {
                $query->where('Status', $status);
            }

            // 3. Lọc theo thể loại (Genre)
            if ($genre) {
                $query->where(function($q) use ($genre) {
                    // Trường hợp 1: Nếu Frontend gửi lên ID (UUID hoặc số)
                    $q->where('GenreId', $genre)
                      // Trường hợp 2: Nếu Frontend gửi lên Tên (vd: "Hành động")
                      // Ta sẽ tìm trong bảng 'genres' xem có tên này không, nếu có thì lấy Movie đó
                      ->orWhereHas('genre', function($subQ) use ($genre) {
                          $subQ->where('Name', 'LIKE', "%{$genre}%");
                      });
                });
            }

            $total      = $query->count();
            $totalPages = (int) ceil($total / $limit);
            
            $items      = $query->orderBy('MovieId', 'desc')
                                ->skip(($page - 1) * $limit)
                                ->take($limit)
                                ->get();

            return [
                'data'       => $items,
                'total'      => $total,
                'page'       => $page,
                'limit'      => $limit,
                'totalPages' => $totalPages,
            ];
        });
    }

    /**
     * Lấy tất cả phim (CÓ PHÂN TRANG)
     */
    public function all($perPage = 1000)
    {
        $page = request()->get('page', 1);
        $cacheKey = "movies_all_page_{$page}_per_{$perPage}";

        return Cache::remember($cacheKey, self::CACHE_TIME, function () use ($perPage) {
            return $this->movie
                ->select('MovieId', 'Title', 'Slug', 'Description', 'Duration', 'ReleaseDate', 
                         'PosterUrl', 'TrailerUrl', 'Language', 'Rated', 'Status', 'GenreId', 'CreatedAt')
                ->with([
                    'genre:GenreId,Name',
                    'directors:CastId,MovieId,Name,Role',
                    'actors:CastId,MovieId,Name,Role',
                ])
                ->orderBy('MovieId', 'desc')
                ->paginate($perPage);
        });
    }

    public function find($movieId)
    {
        return Cache::remember("movie_detail_{$movieId}", self::CACHE_TIME, function () use ($movieId) {
            return $this->movie
                ->with([
                    'genre:GenreId,Name,Description',
                    'directors:CastId,MovieId,Name,Role',
                    'actors:CastId,MovieId,Name,Role',
                ])
                ->find($movieId);
        });
    }

    public function create(array $data)
    {
        $movie = $this->movie->create($data);
        $this->clearCache();
        return $movie;
    }

    public function update(Movie $movie, array $data)
    {
        $movie->update($data);
        $this->clearCache();
        Cache::forget("movie_detail_{$movie->MovieId}");
        Cache::forget("movie_slug_{$movie->MovieId}");
        
        return $movie->fresh([
            'genre:GenreId,Name,Description',
            'directors:CastId,MovieId,Name,Role',
            'actors:CastId,MovieId,Name,Role',
        ]);
    }

    public function delete(Movie $movie)
    {
        $movieId = $movie->MovieId;
        $movie->delete();
        Cache::forget("movie_detail_{$movieId}");
        Cache::forget("movie_slug_{$movieId}");
        $this->clearCache();
        return true;
    }

    public function getNowShowingWithDetails()
    {
        return Cache::remember('movies_now_showing_details', self::CACHE_TIME, function () {
            return $this->movie
                ->select('MovieId', 'Title', 'Slug', 'Description', 'Duration', 'ReleaseDate',
                         'PosterUrl', 'TrailerUrl', 'Language', 'Rated', 'Status', 'GenreId')
                ->where('Status', 'NowShowing')
                ->with([
                    'genre:GenreId,Name,Description',
                    'directors:CastId,MovieId,Name,Role',
                    'actors:CastId,MovieId,Name,Role',
                ])
                ->orderBy('ReleaseDate', 'desc')
                ->limit(50)
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
                        'genre' => $movie->genre ? [
                            'GenreId' => $movie->genre->GenreId,
                            'Name' => $movie->genre->Name,
                            'Description' => $movie->genre->Description
                        ] : null,
                        'directors' => $movie->directors->map(function($director) {
                            return [
                                'CastId' => $director->CastId,
                                'Name' => $director->Name,
                                'Role' => $director->Role
                            ];
                        }),
                        'actors' => $movie->actors->map(function($actor) {
                            return [
                                'CastId' => $actor->CastId,
                                'Name' => $actor->Name,
                                'Role' => $actor->Role
                            ];
                        })
                    ];
                });
        });
    }

    public function getMovieDetailById($movieId)
    {
        return Cache::remember("movie_slug_{$movieId}", self::CACHE_TIME, function () use ($movieId) {
            $movie = $this->movie
                ->where('MovieId', $movieId)
                ->orWhere('Slug', $movieId)
                ->with([
                    'genre:GenreId,Name',
                    'directors:CastId,MovieId,Name,Role',
                    'actors:CastId,MovieId,Name,Role',
                ])
                ->first();

            if (!$movie) {
                return null;
            }

            return [
                'MovieId'     => $movie->MovieId,
                'Title'       => $movie->Title,
                'Slug'        => $movie->Slug,
                'Description' => $movie->Description,
                'Duration'    => $movie->Duration,
                'ReleaseDate' => $movie->ReleaseDate?->format('Y-m-d'),
                'PosterUrl'   => $movie->PosterUrl,
                'TrailerUrl'  => $movie->TrailerUrl,
                'Language'    => $movie->Language,
                'Rated'       => $movie->Rated,
                'Status'      => $movie->Status,
                'genre'       => $movie->genre ? [
                    'GenreId' => $movie->genre->GenreId,
                    'Name'    => $movie->genre->Name
                ] : null,
                'directors'   => $movie->directors->map(fn($d) => [
                    'CastId' => $d->CastId,
                    'Name'   => $d->Name
                ]),
                'actors'      => $movie->actors->map(fn($a) => [
                    'CastId' => $a->CastId,
                    'Name'   => $a->Name
                ])
            ];
        });
    }

    public function getNowShowingByGenre($genreId)
    {
        return Cache::remember("movies_by_genre_{$genreId}", self::CACHE_TIME, function () use ($genreId) {
            return $this->movie
                ->where('Status', 'NowShowing')
                ->where('GenreId', $genreId)
                ->with([
                    'genre:GenreId,Name,Description',
                    'directors:CastId,MovieId,Name,Role',
                    'actors:CastId,MovieId,Name,Role',
                ])
                ->orderBy('ReleaseDate', 'desc')
                ->get();
        });
    }

    public function searchNowShowing($keyword)
    {
        return $this->movie
            ->where('Status', 'NowShowing')
            ->where(function($query) use ($keyword) {
                $query->where('Title', 'LIKE', "%{$keyword}%")
                      ->orWhere('Description', 'LIKE', "%{$keyword}%");
            })
            ->with([
                'genre:GenreId,Name,Description',
                'directors:CastId,MovieId,Name,Role',
                'actors:CastId,MovieId,Name,Role',
            ])
            ->orderBy('ReleaseDate', 'desc')
            ->get();
    }

    public function getComingSoon()
    {
        return Cache::remember('movies_coming_soon', self::CACHE_TIME, function () {
            return $this->movie
                ->where('Status', 'ComingSoon')
                ->with([
                    'genre:GenreId,Name,Description',
                    'directors:CastId,MovieId,Name,Role',
                    'actors:CastId,MovieId,Name,Role',
                ])
                ->orderBy('ReleaseDate', 'asc')
                ->get();
        });
    }

    public function getNowShowingFilters()
    {
        return Cache::remember('movies_now_showing_filters', self::CACHE_TIME, function () {
            $genres = Genre::select('GenreId', 'Name')
                ->whereHas('movies', fn($q) => $q->where('Status', 'NowShowing'))
                ->orderBy('Name')
                ->get();

            $directors = Moviecast::select('CastId', 'Name')
                ->where('Role', 'Director')
                ->whereHas('movie', fn($q) => $q->where('Status', 'NowShowing'))
                ->distinct()
                ->orderBy('Name')
                ->get();

            $actors = Moviecast::select('CastId', 'Name')
                ->where('Role', 'Actor')
                ->whereHas('movie', fn($q) => $q->where('Status', 'NowShowing'))
                ->distinct()
                ->orderBy('Name')
                ->get();

            $totalMovies = $this->movie->where('Status', 'NowShowing')->count();

            return [
                'genres' => $genres,
                'directors' => $directors,
                'actors' => $actors,
                'total_movies' => $totalMovies
            ];
        });
    }

    public function filterNowShowing(array $filters)
    {
        $query = $this->movie
            ->where('Status', 'NowShowing')
            ->with([
                'genre:GenreId,Name,Description',
                'directors:CastId,MovieId,Name,Role',
                'actors:CastId,MovieId,Name,Role',
            ]);

        if (!empty($filters['genre_id'])) {
            $query->where('GenreId', $filters['genre_id']);
        }
        if (!empty($filters['director_name'])) {
            $query->whereHas('directors', function($q) use ($filters) {
                $q->where('Name', 'LIKE', "%{$filters['director_name']}%");
            });
        }
        if (!empty($filters['actor_name'])) {
            $query->whereHas('actors', function($q) use ($filters) {
                $q->where('Name', 'LIKE', "%{$filters['actor_name']}%");
            });
        }
        if (!empty($filters['language'])) {
            $query->where('Language', 'LIKE', "%{$filters['language']}%");
        }
        if (!empty($filters['rated'])) {
            $query->where('Rated', $filters['rated']);
        }

        return $query->orderBy('ReleaseDate', 'desc')->get();
    }

    public function getByStatus($status)
    {
        return Cache::remember("movies_status_{$status}", self::CACHE_TIME, function () use ($status) {
            return $this->movie
                ->where('Status', $status)
                ->with([
                    'genre:GenreId,Name,Description',
                    'directors:CastId,MovieId,Name,Role',
                    'actors:CastId,MovieId,Name,Role',
                ])
                ->orderBy('ReleaseDate', 'desc')
                ->get();
        });
    }

    public function getLatestMovies($limit = 10)
    {
        return Cache::remember("movies_latest_{$limit}", self::CACHE_TIME, function () use ($limit) {
            return $this->movie
                ->with([
                    'genre:GenreId,Name',
                    'directors:CastId,MovieId,Name,Role',
                    'actors:CastId,MovieId,Name,Role',
                ])
                ->orderBy('CreatedAt', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    public function getHotMovies($limit = 10)
    {
        return Cache::remember("movies_hot_{$limit}", self::CACHE_TIME, function () use ($limit) {
            return $this->movie
                ->where('Status', 'NowShowing')
                ->withCount('reviews')
                ->with([
                    'genre:GenreId,Name',
                    'directors:CastId,MovieId,Name,Role',
                    'actors:CastId,MovieId,Name,Role',
                ])
                ->orderBy('reviews_count', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    public function getByIds(array $movieIds)
    {
        $sortedIds = $movieIds;
        sort($sortedIds);
        $cacheKey = 'movies_by_ids_' . md5(implode('_', $sortedIds));

        return Cache::remember($cacheKey, self::CACHE_TIME, function () use ($movieIds) {
            return $this->movie
                ->whereIn('MovieId', $movieIds)
                ->with([
                    'genre:GenreId,Name,Description',
                    'directors:CastId,MovieId,Name,Role',
                    'actors:CastId,MovieId,Name,Role',
                ])
                ->get();
        });
    }

    public function countByStatus()
    {
        return Cache::remember('movies_count_by_status', self::CACHE_TIME, function () {
            return [
                'now_showing' => $this->movie->where('Status', 'NowShowing')->count(),
                'coming_soon' => $this->movie->where('Status', 'ComingSoon')->count(),
                'ended' => $this->movie->where('Status', 'Ended')->count(),
                'total' => $this->movie->count()
            ];
        });
    }

    /**
     * ✅ Xóa Cache khi có thay đổi dữ liệu
     */
    public function clearCache()
    {
        Cache::forget('movies_now_showing_details');
        Cache::forget('movies_coming_soon');
        Cache::forget('movies_now_showing_filters');
        Cache::forget('movies_count_by_status');
        Cache::forget('movies_status_NowShowing');
        Cache::forget('movies_status_ComingSoon');
        Cache::forget('movies_status_Ended');

        foreach ([5, 10, 20, 50] as $limit) {
            Cache::forget("movies_latest_{$limit}");
            Cache::forget("movies_hot_{$limit}");
        }

        for ($page = 1; $page <= 20; $page++) {
            Cache::forget("movies_all_page_{$page}_per_20");
            Cache::forget("movies_all_page_{$page}_per_10");
        }

        // Quét xóa vét cạn cho các trang 1->20, limit 10->100, các trạng thái (bao gồm cả phiên bản v4)
        for ($page = 1; $page <= 20; $page++) {
            for ($limit = 10; $limit <= 100; $limit += 10) {
                Cache::forget("movies_paged_v4_p{$page}_l{$limit}_" . md5('' . ''));
                Cache::forget("movies_paged_v4_p{$page}_l{$limit}_" . md5('' . 'NowShowing'));
                Cache::forget("movies_paged_v4_p{$page}_l{$limit}_" . md5('' . 'ComingSoon'));
                Cache::forget("movies_paged_v4_p{$page}_l{$limit}_" . md5('' . 'Ended'));
            }
        }
    }
}