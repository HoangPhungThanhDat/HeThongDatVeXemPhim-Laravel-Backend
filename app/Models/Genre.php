<?php

// /**
//  * Created by Reliese Model.
//  */

// namespace App\Models;

// use Carbon\Carbon;
// use Illuminate\Database\Eloquent\Collection;
// use Illuminate\Database\Eloquent\Model;

// /**
//  * Class Genre
//  * 
//  * @property int $GenreId
//  * @property string $Name
//  * @property string|null $Description
//  * @property string $Status
//  * @property Carbon $CreatedAt
//  * @property Carbon $UpdatedAt
//  * @property int|null $CreatedBy
//  * @property int|null $UpdatedBy
//  * 
//  * @property User|null $user
//  * @property Collection|Movie[] $movies
//  *
//  * @package App\Models
//  */
// class Genre extends Model
// {
// 	protected $table = 'genres';
// 	protected $primaryKey = 'GenreId';
// 	public $timestamps = false;

// 	protected $casts = [
// 		'CreatedAt' => 'datetime',
// 		'UpdatedAt' => 'datetime',
// 		'CreatedBy' => 'int',
// 		'UpdatedBy' => 'int'
// 	];

// 	protected $fillable = [
// 		'Name',
// 		'Description',
// 		'Status',
// 		'CreatedAt',
// 		'UpdatedAt',
// 		'CreatedBy',
// 		'UpdatedBy'
// 	];

// 	public function user()
// 	{
// 		return $this->belongsTo(User::class, 'UpdatedBy');
// 	}

// 	public function movies()
// 	{
// 		return $this->belongsToMany(Movie::class, 'moviegenres', 'GenreId', 'MovieId')
// 					->withPivot('MovieGenreId', 'Status', 'CreatedAt', 'UpdatedAt', 'CreatedBy', 'UpdatedBy');
// 	}
// }






























namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Genre
 * 
 * @property int $GenreId
 * @property string $Name
 * @property string|null $Description
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property User|null $user
 * @property Collection|Movie[] $movies
 * @property Collection|Movie[] $moviesViaPivot
 *
 * @package App\Models
 */
class Genre extends Model
{
    protected $table = 'genres';
    protected $primaryKey = 'GenreId';
    public $incrementing = true;
    protected $keyType = 'int';
    
    // Bật timestamps với tên cột tùy chỉnh
    public $timestamps = true;
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    protected $casts = [
        'GenreId' => 'int',
        'CreatedAt' => 'datetime',
        'UpdatedAt' => 'datetime',
        'CreatedBy' => 'int',
        'UpdatedBy' => 'int'
    ];

    protected $fillable = [
        'Name',
        'Description',
        'Status',
        'CreatedBy',
        'UpdatedBy'
    ];

    /**
     * Relationship: Genre được tạo/cập nhật bởi User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UpdatedBy');
    }

    /**
     * Relationship: Genre có nhiều Movies (quan hệ trực tiếp qua GenreId)
     * Đây là quan hệ One-to-Many
     * 
     * Sử dụng: $genre->movies
     */
    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class, 'GenreId', 'GenreId');
    }

    /**
     * Relationship: Genre có nhiều Movies (qua bảng moviegenres)
     * Đây là quan hệ Many-to-Many
     * 
     * Sử dụng: $genre->moviesViaPivot
     */
    public function moviesViaPivot(): BelongsToMany
    {
        return $this->belongsToMany(
            Movie::class,
            'moviegenres',      // Tên bảng trung gian
            'GenreId',          // Foreign key của Genre trong bảng trung gian
            'MovieId',          // Foreign key của Movie trong bảng trung gian
            'GenreId',          // Primary key của Genre
            'MovieId'           // Primary key của Movie
        )
        ->withPivot('MovieGenreId', 'Status', 'CreatedAt', 'UpdatedAt', 'CreatedBy', 'UpdatedBy')
        ->wherePivot('Status', 'Active');
    }

    /**
     * Relationship: Lấy tất cả movies (cả trực tiếp lẫn qua pivot)
     * Sử dụng cho trường hợp tổng hợp
     */
    public function allMovies()
    {
        // Merge cả hai relationships
        return $this->movies()
            ->union($this->moviesViaPivot()->getQuery())
            ->distinct();
    }

    /**
     * Scope: Lọc theo trạng thái Active
     * 
     * Sử dụng: Genre::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('Status', 'Active');
    }

    /**
     * Scope: Tìm kiếm theo tên
     * 
     * Sử dụng: Genre::search('Hành động')->get()
     */
    public function scopeSearch($query, $keyword)
    {
        return $query->where(function($q) use ($keyword) {
            $q->where('Name', 'LIKE', "%{$keyword}%")
              ->orWhere('Description', 'LIKE', "%{$keyword}%");
        });
    }

    /**
     * Scope: Sắp xếp theo số lượng phim (nhiều nhất)
     * 
     * Sử dụng: Genre::orderByMovieCount()->get()
     */
    public function scopeOrderByMovieCount($query, $direction = 'desc')
    {
        return $query->withCount('movies')
                     ->orderBy('movies_count', $direction);
    }

    /**
     * Accessor: Đếm số lượng phim của thể loại
     */
    public function getMoviesCountAttribute()
    {
        return $this->movies()->count();
    }

    /**
     * Accessor: Lấy số lượng phim đang chiếu
     */
    public function getNowShowingCountAttribute()
    {
        return $this->movies()
                    ->where('Status', 'NowShowing')
                    ->count();
    }

    /**
     * Accessor: Kiểm tra có phim đang chiếu không
     */
    public function getHasNowShowingAttribute()
    {
        return $this->movies()
                    ->where('Status', 'NowShowing')
                    ->exists();
    }

    /**
     * Accessor: Lấy tên thể loại viết hoa
     */
    public function getNameUpperAttribute()
    {
        return strtoupper($this->Name);
    }

    /**
     * Method: Lấy phim đang chiếu của thể loại này
     */
    public function nowShowingMovies()
    {
        return $this->movies()
                    ->where('Status', 'NowShowing')
                    ->with(['directors', 'actors'])
                    ->orderBy('ReleaseDate', 'desc')
                    ->get();
    }

    /**
     * Method: Lấy phim sắp chiếu của thể loại này
     */
    public function comingSoonMovies()
    {
        return $this->movies()
                    ->where('Status', 'ComingSoon')
                    ->with(['directors', 'actors'])
                    ->orderBy('ReleaseDate', 'asc')
                    ->get();
    }
}