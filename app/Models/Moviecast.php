<?php

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

    // ==================== RELATIONSHIPS ====================

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

    // ==================== SCOPES ====================

    /**
     * Scope: Lọc theo vai trò
     */
    public function scopeRole($query, $role)
    {
        return $query->where('Role', $role);
    }

    /**
     * Scope: Chỉ lấy Đạo diễn
     */
    public function scopeDirectors($query)
    {
        return $query->where('Role', 'Director');
    }

    /**
     * Scope: Chỉ lấy Diễn viên
     */
    public function scopeActors($query)
    {
        return $query->where('Role', 'Actor');
    }

    /**
     * Scope: Lọc theo trạng thái Active
     */
    public function scopeActive($query)
    {
        return $query->where('Status', 'Active');
    }

    /**
     * Scope: Tìm kiếm theo tên
     */
    public function scopeSearchByName($query, $name)
    {
        return $query->where('Name', 'LIKE', "%{$name}%");
    }

    // ==================== ACCESSORS ====================

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