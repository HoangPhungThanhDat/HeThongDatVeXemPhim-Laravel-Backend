<?php

// /**
//  * Created by Reliese Model.
//  */

// namespace App\Models;

// use Carbon\Carbon;
// use Illuminate\Database\Eloquent\Model;

// /**
//  * Class Moviecast
//  * 
//  * @property int $CastId
//  * @property int $MovieId
//  * @property string $Name
//  * @property string $Role
//  * @property string $Status
//  * @property Carbon $CreatedAt
//  * @property Carbon $UpdatedAt
//  * @property int|null $CreatedBy
//  * @property int|null $UpdatedBy
//  * 
//  * @property Movie $movie
//  * @property User|null $user
//  *
//  * @package App\Models
//  */
// class Moviecast extends Model
// {
// 	protected $table = 'moviecast';
// 	protected $primaryKey = 'CastId';
// 	public $timestamps = false;

// 	protected $casts = [
// 		'MovieId' => 'int',
// 		'CreatedAt' => 'datetime',
// 		'UpdatedAt' => 'datetime',
// 		'CreatedBy' => 'int',
// 		'UpdatedBy' => 'int'
// 	];

// 	protected $fillable = [
// 		'MovieId',
// 		'Name',
// 		'Role',
// 		'Status',
// 		'CreatedAt',
// 		'UpdatedAt',
// 		'CreatedBy',
// 		'UpdatedBy'
// 	];

// 	public function movie()
// 	{
// 		return $this->belongsTo(Movie::class, 'MovieId');
// 	}

// 	public function user()
// 	{
// 		return $this->belongsTo(User::class, 'UpdatedBy');
// 	}
// }

















namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Moviecast
 * 
 * @property int $CastId
 * @property int $MovieId
 * @property string $Name
 * @property string $Role
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property Movie $movie
 * @property User|null $user
 *
 * @package App\Models
 */
class Moviecast extends Model
{
    protected $table = 'moviecast';
    protected $primaryKey = 'CastId';
    public $incrementing = true;
    protected $keyType = 'int';
    
    // Bật timestamps với tên cột tùy chỉnh
    public $timestamps = true;
    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    protected $casts = [
        'CastId' => 'int',
        'MovieId' => 'int',
        'CreatedAt' => 'datetime',
        'UpdatedAt' => 'datetime',
        'CreatedBy' => 'int',
        'UpdatedBy' => 'int'
    ];

    protected $fillable = [
        'MovieId',
        'Name',
        'Role',
        'Status',
        'CreatedBy',
        'UpdatedBy'
    ];

    /**
     * Relationship: Cast thuộc về một Movie
     */
    public function movie(): BelongsTo
    {
        return $this->belongsTo(Movie::class, 'MovieId', 'MovieId');
    }

    /**
     * Relationship: Cast được tạo/cập nhật bởi User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UpdatedBy');
    }

    /**
     * Scope: Lọc theo vai trò
     * 
     * Sử dụng: Moviecast::role('Director')->get()
     */
    public function scopeRole($query, $role)
    {
        return $query->where('Role', $role);
    }

    /**
     * Scope: Chỉ lấy Đạo diễn
     * 
     * Sử dụng: Moviecast::directors()->get()
     */
    public function scopeDirectors($query)
    {
        return $query->where('Role', 'Director');
    }

    /**
     * Scope: Chỉ lấy Diễn viên
     * 
     * Sử dụng: Moviecast::actors()->get()
     */
    public function scopeActors($query)
    {
        return $query->where('Role', 'Actor');
    }

    /**
     * Scope: Lọc theo trạng thái Active
     * 
     * Sử dụng: Moviecast::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('Status', 'Active');
    }

    /**
     * Scope: Tìm kiếm theo tên
     * 
     * Sử dụng: Moviecast::searchByName('Jason')->get()
     */
    public function scopeSearchByName($query, $name)
    {
        return $query->where('Name', 'LIKE', "%{$name}%");
    }

    /**
     * Accessor: Kiểm tra có phải đạo diễn không
     */
    public function getIsDirectorAttribute()
    {
        return $this->Role === 'Director';
    }

    /**
     * Accessor: Kiểm tra có phải diễn viên không
     */
    public function getIsActorAttribute()
    {
        return $this->Role === 'Actor';
    }

    /**
     * Accessor: Lấy tên vai trò tiếng Việt
     */
    public function getRoleNameAttribute()
    {
        return match($this->Role) {
            'Director' => 'Đạo diễn',
            'Actor' => 'Diễn viên',
            'Producer' => 'Nhà sản xuất',
            'Writer' => 'Biên kịch',
            default => $this->Role
        };
    }
}