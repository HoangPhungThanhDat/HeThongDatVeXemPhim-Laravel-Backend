<?php

// /**
//  * Created by Reliese Model.
//  */

// namespace App\Models;

// use Carbon\Carbon;
// use Illuminate\Database\Eloquent\Collection;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Support\Str;

// /**
//  * Class Movie
//  * 
//  * @property int $MovieId
//  * @property string $Title
//  * @property string $Slug
//  * @property string|null $Description
//  * @property string|null $Genre
//  * @property int|null $Duration
//  * @property Carbon|null $ReleaseDate
//  * @property string|null $PosterUrl
//  * @property string|null $TrailerUrl
//  * @property string|null $Language
//  * @property string|null $Rated
//  * @property string $Status
//  * @property Carbon $CreatedAt
//  * @property Carbon $UpdatedAt
//  * @property int|null $CreatedBy
//  * @property int|null $UpdatedBy
//  * 
//  * @property User|null $user
//  * @property Collection|Moviecast[] $moviecasts
//  * @property Collection|Genre[] $genres
//  * @property Collection|Review[] $reviews
//  * @property Collection|Schedule[] $schedules
//  * @property Collection|Showtime[] $showtimes
//  * @property Collection|Wishlist[] $wishlists
//  *
//  * @package App\Models
//  */
// class Movie extends Model
// {
// 	protected $table = 'movies';
// 	protected $primaryKey = 'MovieId';
// 	public $incrementing = true;
// 	protected $keyType = 'int';
// 	public $timestamps = false;

// 	protected $casts = [
// 		'MovieId' => 'int',
// 		'GenreId' => 'int',
// 		'Duration' => 'int',
// 		'ReleaseDate' => 'datetime',
// 		'CreatedAt' => 'datetime',
// 		'UpdatedAt' => 'datetime',
// 		'CreatedBy' => 'int',
// 		'UpdatedBy' => 'int'
// 	];

// 	protected $fillable = [
// 		'Title',
// 		'Slug',
// 		'Description',
// 		'GenreId',
// 		'Duration',
// 		'ReleaseDate',
// 		'PosterUrl',
// 		'TrailerUrl',
// 		'Language',
// 		'Rated',
// 		'Status',
// 		'CreatedAt',
// 		'UpdatedAt',
// 		'CreatedBy',
// 		'UpdatedBy'
// 	];
// 	protected static function boot()
// 	{
// 		parent::boot();

// 		static::creating(function ($movie) {
// 		  if (empty($movie->Slug) && !empty($movie->Title)) {
//                 $movie->Slug = Str::slug($movie->Title);
//             }
//         });

// 		 static::updating(function ($movie) {
//             if (empty($movie->Slug) && !empty($movie->Title)) {
//                 $movie->Slug = Str::slug($movie->Title);
//             }
//         });
// 	}
		
// 	public function user()
// 	{
// 		return $this->belongsTo(User::class, 'UpdatedBy');
// 	}

// 	public function moviecasts()
// 	{
// 		return $this->hasMany(Moviecast::class, 'MovieId');
// 	}

// 	public function genre()
// 	{
// 		return $this->belongsTo(Genre::class, 'GenreId', 'GenreId');
// 	}

// 	public function reviews()
// 	{
// 		return $this->hasMany(Review::class, 'MovieId');
// 	}

// 	public function schedules()
// 	{
// 		return $this->hasMany(Schedule::class, 'MovieId');
// 	}

// 	public function showtimes()
// 	{
// 		return $this->hasMany(Showtime::class, 'MovieId');
// 	}

// 	public function wishlists()
// 	{
// 		return $this->hasMany(Wishlist::class, 'MovieId');
// 	}
// }




















// namespace App\Models;

// use Carbon\Carbon;
// use Illuminate\Database\Eloquent\Collection;
// use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Relations\HasMany;
// use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Illuminate\Support\Str;

// /**
//  * Class Movie
//  * 
//  * @property int $MovieId
//  * @property string $Title
//  * @property string $Slug
//  * @property string|null $Description
//  * @property int|null $GenreId
//  * @property int|null $Duration
//  * @property Carbon|null $ReleaseDate
//  * @property string|null $PosterUrl
//  * @property string|null $TrailerUrl
//  * @property string|null $Language
//  * @property string|null $Rated
//  * @property string $Status
//  * @property Carbon $CreatedAt
//  * @property Carbon $UpdatedAt
//  * @property int|null $CreatedBy
//  * @property int|null $UpdatedBy
//  * 
//  * @property User|null $user
//  * @property Genre|null $genre
//  * @property Collection|Moviecast[] $moviecasts
//  * @property Collection|Review[] $reviews
//  * @property Collection|Schedule[] $schedules
//  * @property Collection|Showtime[] $showtimes
//  * @property Collection|Wishlist[] $wishlists
//  *
//  * @package App\Models
//  */
// class Movie extends Model
// {
//     protected $table = 'movies';
//     protected $primaryKey = 'MovieId';
//     public $incrementing = true;
//     protected $keyType = 'int';
    
//     // Sử dụng timestamps tùy chỉnh
//     public $timestamps = true;
//     const CREATED_AT = 'CreatedAt';
//     const UPDATED_AT = 'UpdatedAt';

//     protected $casts = [
//         'MovieId' => 'int',
//         'GenreId' => 'int',
//         'Duration' => 'int',
//         'ReleaseDate' => 'datetime',
//         'CreatedAt' => 'datetime',
//         'UpdatedAt' => 'datetime',
//         'CreatedBy' => 'int',
//         'UpdatedBy' => 'int'
//     ];

//     protected $fillable = [
//         'Title',
//         'Slug',
//         'Description',
//         'GenreId',
//         'Duration',
//         'ReleaseDate',
//         'PosterUrl',
//         'TrailerUrl',
//         'Language',
//         'Rated',
//         'Status',
//         'CreatedBy',
//         'UpdatedBy'
//     ];

//     protected static function boot()
//     {
//         parent::boot();

//         static::creating(function ($movie) {
//             if (empty($movie->Slug) && !empty($movie->Title)) {
//                 $movie->Slug = Str::slug($movie->Title);
//             }
//         });

//         static::updating(function ($movie) {
//             if (empty($movie->Slug) && !empty($movie->Title)) {
//                 $movie->Slug = Str::slug($movie->Title);
//             }
//         });
//     }
    
//     /**
//      * Relationship: Movie belongs to User (người tạo/cập nhật)
//      */
//     public function user(): BelongsTo
//     {
//         return $this->belongsTo(User::class, 'UpdatedBy');
//     }

//     /**
//      * Relationship: Movie belongs to Genre (thể loại)
//      * Quan hệ Many-to-One
//      */
//     public function genre(): BelongsTo
//     {
//         return $this->belongsTo(Genre::class, 'GenreId', 'GenreId');
//     }

//     /**
//      * Relationship: Movie has many Moviecast (diễn viên, đạo diễn)
//      * One-to-Many
//      */
//     public function moviecasts(): HasMany
//     {
//         return $this->hasMany(Moviecast::class, 'MovieId', 'MovieId');
//     }

//     /**
//      * Relationship: Chỉ lấy các Đạo diễn
//      */
//     public function directors(): HasMany
//     {
//         return $this->hasMany(Moviecast::class, 'MovieId', 'MovieId')
//                     ->where('Role', 'Director')
//                     ->where('Status', 'Active');
//     }

//     /**
//      * Relationship: Chỉ lấy các Diễn viên
//      */
//     public function actors(): HasMany
//     {
//         return $this->hasMany(Moviecast::class, 'MovieId', 'MovieId')
//                     ->where('Role', 'Actor')
//                     ->where('Status', 'Active');
//     }

//     /**
//      * Relationship: Movie has many Reviews
//      */
//     public function reviews(): HasMany
//     {
//         return $this->hasMany(Review::class, 'MovieId');
//     }

//     /**
//      * Relationship: Movie has many Schedules
//      */
//     public function schedules(): HasMany
//     {
//         return $this->hasMany(Schedule::class, 'MovieId');
//     }

//     /**
//      * Relationship: Movie has many Showtimes
//      */
//     public function showtimes(): HasMany
//     {
//         return $this->hasMany(Showtime::class, 'MovieId');
//     }

//     /**
//      * Relationship: Movie has many Wishlists
//      */
//     public function wishlists(): HasMany
//     {
//         return $this->hasMany(Wishlist::class, 'MovieId');
//     }

//     /**
//      * Scope: Lọc phim đang chiếu
//      */
//     public function scopeNowShowing($query)
//     {
//         return $query->where('Status', 'NowShowing');
//     }

//     /**
//      * Scope: Lọc phim sắp chiếu
//      */
//     public function scopeComingSoon($query)
//     {
//         return $query->where('Status', 'ComingSoon');
//     }

//     /**
//      * Scope: Tìm kiếm phim theo từ khóa
//      */
//     public function scopeSearch($query, $keyword)
//     {
//         return $query->where(function($q) use ($keyword) {
//             $q->where('Title', 'LIKE', "%{$keyword}%")
//               ->orWhere('Description', 'LIKE', "%{$keyword}%");
//         });
//     }

//     /**
//      * Accessor: Lấy URL đầy đủ của poster
//      */
//     public function getPosterUrlFullAttribute()
//     {
//         return $this->PosterUrl ? url($this->PosterUrl) : null;
//     }

//     /**
//      * Accessor: Lấy URL đầy đủ của trailer
//      */
//     public function getTrailerUrlFullAttribute()
//     {
//         return $this->TrailerUrl ? url($this->TrailerUrl) : null;
//     }

//     /**
//      * Accessor: Format thời lượng phim
//      */
//     public function getDurationFormattedAttribute()
//     {
//         if (!$this->Duration) return null;
        
//         $hours = floor($this->Duration / 60);
//         $minutes = $this->Duration % 60;
        
//         if ($hours > 0) {
//             return "{$hours}h {$minutes}m";
//         }
//         return "{$minutes}m";
//     }

//     /**
//      * Accessor: Lấy tên thể loại
//      */
//     public function getGenreNameAttribute()
//     {
//         return $this->genre ? $this->genre->Name : 'N/A';
//     }
// }


































namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * Class Movie
 * 
 * @property int $MovieId
 * @property string $Title
 * @property string $Slug
 * @property string|null $Description
 * @property int|null $GenreId
 * @property int|null $Duration
 * @property Carbon|null $ReleaseDate
 * @property string|null $PosterUrl
 * @property string|null $TrailerUrl
 * @property string|null $Language
 * @property string|null $Rated
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property User|null $user
 * @property Genre|null $genre
 * @property Collection|Moviecast[] $moviecasts
 * @property Collection|Review[] $reviews
 * @property Collection|Schedule[] $schedules
 * @property Collection|Showtime[] $showtimes
 * @property Collection|Wishlist[] $wishlists
 *
 * @package App\Models
 */
class Movie extends Model
{
    protected $table = 'movies';
    protected $primaryKey = 'MovieId';
    public $incrementing = true;
    protected $keyType = 'int';
    
    public $timestamps = true;
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    protected $casts = [
        'MovieId' => 'int',
        'GenreId' => 'int',
        'Duration' => 'int',
        'ReleaseDate' => 'datetime',
        'CreatedAt' => 'datetime',
        'UpdatedAt' => 'datetime',
        'CreatedBy' => 'int',
        'UpdatedBy' => 'int'
    ];

    protected $fillable = [
        'Title',
        'Slug',
        'Description',
        'GenreId',
        'Duration',
        'ReleaseDate',
        'PosterUrl',
        'TrailerUrl',
        'Language',
        'Rated',
        'Status',
        'CreatedBy',
        'UpdatedBy'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($movie) {
            if (empty($movie->Slug) && !empty($movie->Title)) {
                $movie->Slug = Str::slug($movie->Title);
            }
        });

        static::updating(function ($movie) {
            if (empty($movie->Slug) && !empty($movie->Title)) {
                $movie->Slug = Str::slug($movie->Title);
            }
        });
    }
    
    // ==================== AUTO UPDATE STATUS ====================

    /**
     * ✅ Cập nhật trạng thái cho phim hiện tại
     * 
     * @return bool True nếu trạng thái thay đổi
     */
    public function updateStatus(): bool
    {
        $today = Carbon::now()->startOfDay();
        $release = Carbon::parse($this->ReleaseDate)->startOfDay();
        
        // ✅ Sửa: Tính số ngày đã qua (lấy giá trị tuyệt đối)
        $daysSinceRelease = abs($today->diffInDays($release));
        
        // Kiểm tra suất chiếu trong tương lai
        $hasShowtime = $this->showtimes()
            ->where('StartTime', '>=', Carbon::now())
            ->exists();
        
        // Tính toán trạng thái mới
        $newStatus = $this->calculateStatus($release, $hasShowtime, $daysSinceRelease);
        
        // Cập nhật nếu khác
        if ($this->Status !== $newStatus) {
            $oldStatus = $this->Status;
            $this->Status = $newStatus;
            $this->save();
            
            // Log sự thay đổi
            Log::info("Movie status updated", [
                'movie_id' => $this->MovieId,
                'title' => $this->Title,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'release_date' => $release->format('Y-m-d'),
                'days_since_release' => $daysSinceRelease,
                'has_showtime' => $hasShowtime
            ]);
            
            return true;
        }
        
        return false;
    }

    /**
     * ✅ Tính toán trạng thái dựa trên ngày chiếu, suất chiếu và số ngày đã qua
     * 
     * @param Carbon $releaseDate Ngày phát hành
     * @param bool $hasShowtime Có suất chiếu trong tương lai không
     * @param int $daysSinceRelease Số ngày đã qua từ ngày phát hành
     * @return string
     */
    private function calculateStatus(Carbon $releaseDate, bool $hasShowtime, int $daysSinceRelease): string
    {
        $today = Carbon::now()->startOfDay();
        
        // 1. Nếu chưa đến ngày chiếu → Sắp chiếu
        if ($releaseDate->gt($today)) {
            return 'ComingSoon';
        }
        
        // 2. Nếu đã đến ngày chiếu hoặc đã qua
        // ✅ ƯU TIÊN 1: Kiểm tra thời gian đã qua
        // Nếu đã qua 60 ngày (2 tháng) → Kết thúc
        if ($daysSinceRelease >= 60) {
            return 'Ended';
        }
        
        // ✅ ƯU TIÊN 2: Kiểm tra suất chiếu
        // Nếu có suất chiếu trong tương lai → Đang chiếu
        if ($hasShowtime) {
            return 'NowShowing';
        }
        
        // 3. Không có suất chiếu và dưới 60 ngày → Đang chiếu (mặc định)
        return 'NowShowing';
    }

    /**
     * ✅ Cập nhật trạng thái cho tất cả phim (static method)
     * 
     * @return array
     */
    public static function autoUpdateAllStatus(): array
    {
        try {
            $movies = self::all();
            $updatedCount = 0;
            $updates = [];
            
            foreach ($movies as $movie) {
                $oldStatus = $movie->Status;
                $changed = $movie->updateStatus();
                
                if ($changed) {
                    $updatedCount++;
                    $updates[] = [
                        'movie_id' => $movie->MovieId,
                        'title' => $movie->Title,
                        'old_status' => $oldStatus,
                        'new_status' => $movie->Status
                    ];
                }
            }
            
            // Log tổng kết
            Log::info('Auto update status completed', [
                'total_movies' => $movies->count(),
                'updated_count' => $updatedCount,
                'updates' => $updates
            ]);
            
            return [
                'success' => true,
                'message' => "Đã cập nhật trạng thái cho {$updatedCount} phim",
                'updated_count' => $updatedCount,
                'updates' => $updates
            ];
        } catch (\Exception $e) {
            Log::error('Lỗi autoUpdateAllStatus: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage(),
                'updated_count' => 0,
                'updates' => []
            ];
        }
    }

    /**
     * ✅ Kiểm tra và cập nhật trạng thái cho phim (force update)
     * 
     * @return bool
     */
    public function forceUpdateStatus(): bool
    {
        $today = Carbon::now()->startOfDay();
        $release = Carbon::parse($this->ReleaseDate)->startOfDay();
        
        // ✅ Sửa: Tính số ngày đã qua (lấy giá trị tuyệt đối)
        $daysSinceRelease = abs($today->diffInDays($release));
        
        // Force tính toán
        if ($release->gt($today)) {
            $newStatus = 'ComingSoon';
        } elseif ($daysSinceRelease >= 60) {
            $newStatus = 'Ended';
        } else {
            $newStatus = 'NowShowing';
        }
        
        if ($this->Status !== $newStatus) {
            $oldStatus = $this->Status;
            $this->Status = $newStatus;
            $this->save();
            
            Log::info("Movie status force updated", [
                'movie_id' => $this->MovieId,
                'title' => $this->Title,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'release_date' => $release->format('Y-m-d'),
                'days_since_release' => $daysSinceRelease
            ]);
            
            return true;
        }
        
        return false;
    }

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UpdatedBy');
    }

    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class, 'GenreId', 'GenreId');
    }

    public function moviecasts(): HasMany
    {
        return $this->hasMany(Moviecast::class, 'MovieId', 'MovieId');
    }

    public function directors(): HasMany
    {
        return $this->hasMany(Moviecast::class, 'MovieId', 'MovieId')
                    ->where('Role', 'Director')
                    ->where('Status', 'Active');
    }

    public function actors(): HasMany
    {
        return $this->hasMany(Moviecast::class, 'MovieId', 'MovieId')
                    ->where('Role', 'Actor')
                    ->where('Status', 'Active');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'MovieId');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'MovieId');
    }

    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class, 'MovieId');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'MovieId');
    }

    // ==================== SCOPES ====================

    public function scopeNowShowing($query)
    {
        return $query->where('Status', 'NowShowing');
    }

    public function scopeComingSoon($query)
    {
        return $query->where('Status', 'ComingSoon');
    }

    public function scopeEnded($query)
    {
        return $query->where('Status', 'Ended');
    }

    public function scopeByStatus($query, $status)
    {
        if ($status && $status !== 'Tất cả') {
            return $query->where('Status', $status);
        }
        return $query;
    }

    public function scopeSearch($query, $keyword)
    {
        return $query->where(function($q) use ($keyword) {
            $q->where('Title', 'LIKE', "%{$keyword}%")
              ->orWhere('Description', 'LIKE', "%{$keyword}%");
        });
    }

    // ==================== ACCESSORS ====================

    public function getPosterUrlFullAttribute()
    {
        return $this->PosterUrl ? url($this->PosterUrl) : null;
    }

    public function getTrailerUrlFullAttribute()
    {
        return $this->TrailerUrl ? url($this->TrailerUrl) : null;
    }

    public function getDurationFormattedAttribute()
    {
        if (!$this->Duration) return null;
        
        $hours = floor($this->Duration / 60);
        $minutes = $this->Duration % 60;
        
        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }
        return "{$minutes}m";
    }

    public function getGenreNameAttribute()
    {
        return $this->genre ? $this->genre->Name : 'N/A';
    }

    public function getStatusLabelAttribute()
    {
        $labels = [
            'NowShowing' => 'Đang chiếu',
            'ComingSoon' => 'Sắp chiếu',
            'Ended' => 'Đã kết thúc'
        ];
        return $labels[$this->Status] ?? $this->Status;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'NowShowing' => 'success',
            'ComingSoon' => 'warning',
            'Ended' => 'danger'
        ];
        return $colors[$this->Status] ?? 'secondary';
    }

    /**
     * ✅ Accessor: Lấy số ngày đã qua từ ngày phát hành
     */
    public function getDaysSinceReleaseAttribute()
    {
        if (!$this->ReleaseDate) return null;
        $today = Carbon::now()->startOfDay();
        $release = Carbon::parse($this->ReleaseDate)->startOfDay();
        return abs($today->diffInDays($release));
    }

    /**
     * ✅ Accessor: Kiểm tra phim có đang chiếu không
     */
    public function getIsNowShowingAttribute()
    {
        return $this->Status === 'NowShowing';
    }

    /**
     * ✅ Accessor: Kiểm tra phim có sắp chiếu không
     */
    public function getIsComingSoonAttribute()
    {
        return $this->Status === 'ComingSoon';
    }

    /**
     * ✅ Accessor: Kiểm tra phim đã kết thúc chưa
     */
    public function getIsEndedAttribute()
    {
        return $this->Status === 'Ended';
    }





    /**
 * ✅ Relationship với Moviecast - dùng cho tất cả cast members
 */
    public function castMembers(): HasMany
    {
        return $this->hasMany(Moviecast::class, 'MovieId', 'MovieId');
    }


    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'moviegenres', 'MovieId', 'GenreId');
    }
}