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




















namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

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
    
    // Sử dụng timestamps tùy chỉnh
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
    
    /**
     * Relationship: Movie belongs to User (người tạo/cập nhật)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UpdatedBy');
    }

    /**
     * Relationship: Movie belongs to Genre (thể loại)
     * Quan hệ Many-to-One
     */
    public function genre(): BelongsTo
    {
        return $this->belongsTo(Genre::class, 'GenreId', 'GenreId');
    }

    /**
     * Relationship: Movie has many Moviecast (diễn viên, đạo diễn)
     * One-to-Many
     */
    public function moviecasts(): HasMany
    {
        return $this->hasMany(Moviecast::class, 'MovieId', 'MovieId');
    }

    /**
     * Relationship: Chỉ lấy các Đạo diễn
     */
    public function directors(): HasMany
    {
        return $this->hasMany(Moviecast::class, 'MovieId', 'MovieId')
                    ->where('Role', 'Director')
                    ->where('Status', 'Active');
    }

    /**
     * Relationship: Chỉ lấy các Diễn viên
     */
    public function actors(): HasMany
    {
        return $this->hasMany(Moviecast::class, 'MovieId', 'MovieId')
                    ->where('Role', 'Actor')
                    ->where('Status', 'Active');
    }

    /**
     * Relationship: Movie has many Reviews
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'MovieId');
    }

    /**
     * Relationship: Movie has many Schedules
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'MovieId');
    }

    /**
     * Relationship: Movie has many Showtimes
     */
    public function showtimes(): HasMany
    {
        return $this->hasMany(Showtime::class, 'MovieId');
    }

    /**
     * Relationship: Movie has many Wishlists
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class, 'MovieId');
    }

    /**
     * Scope: Lọc phim đang chiếu
     */
    public function scopeNowShowing($query)
    {
        return $query->where('Status', 'NowShowing');
    }

    /**
     * Scope: Lọc phim sắp chiếu
     */
    public function scopeComingSoon($query)
    {
        return $query->where('Status', 'ComingSoon');
    }

    /**
     * Scope: Tìm kiếm phim theo từ khóa
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function($q) use ($keyword) {
            $q->where('Title', 'LIKE', "%{$keyword}%")
              ->orWhere('Description', 'LIKE', "%{$keyword}%");
        });
    }

    /**
     * Accessor: Lấy URL đầy đủ của poster
     */
    public function getPosterUrlFullAttribute()
    {
        return $this->PosterUrl ? url($this->PosterUrl) : null;
    }

    /**
     * Accessor: Lấy URL đầy đủ của trailer
     */
    public function getTrailerUrlFullAttribute()
    {
        return $this->TrailerUrl ? url($this->TrailerUrl) : null;
    }

    /**
     * Accessor: Format thời lượng phim
     */
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

    /**
     * Accessor: Lấy tên thể loại
     */
    public function getGenreNameAttribute()
    {
        return $this->genre ? $this->genre->Name : 'N/A';
    }
}