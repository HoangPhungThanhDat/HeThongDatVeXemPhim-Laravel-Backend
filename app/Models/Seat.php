<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Seat
 * 
 * @property int $SeatId
 * @property int $RoomId
 * @property string $Row
 * @property int $Number
 * @property string $SeatType
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property Room $room
 * @property User|null $user
 * @property Collection|ShowtimeSeat[] $showtimeSeats
 * @property Collection|Showtime[] $showtimes
 * @property Collection|Ticket[] $tickets
 *
 * @package App\Models
 */
class Seat extends Model
{
	protected $table = 'seats';
	protected $primaryKey = 'SeatId';
	public $timestamps = false;

	protected $casts = [
		'RoomId' => 'int',
		'Number' => 'int',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'RoomId',
		'Row',
		'Number',
		'SeatType',
		'Status',
		'CreatedAt',
		'UpdatedAt',
		'CreatedBy',
		'UpdatedBy'
	];

	// ============================================
	// RELATIONSHIPS
	// ============================================

	/**
	 * Phòng chiếu chứa ghế này
	 */
	public function room()
	{
		return $this->belongsTo(Room::class, 'RoomId');
	}

	/**
	 * Người dùng cập nhật ghế
	 */
	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}

	/**
	 * Trạng thái ghế theo từng suất chiếu
	 * (Quan trọng cho việc đặt vé)
	 */
	public function showtimeSeats()
	{
		return $this->hasMany(ShowtimeSeat::class, 'SeatId');
	}

	/**
	 * Các suất chiếu có ghế này (qua bảng showtimeseats)
	 */
	public function showtimes()
	{
		return $this->belongsToMany(Showtime::class, 'showtimeseats', 'SeatId', 'ShowtimeId')
					->withPivot('ShowtimeSeatId', 'Status', 'CreatedAt', 'UpdatedAt', 'CreatedBy', 'UpdatedBy')
					->withTimestamps();
	}

	/**
	 * Các vé đã đặt cho ghế này
	 */
	public function tickets()
	{
		return $this->hasMany(Ticket::class, 'SeatId');
	}

	// ============================================
	// SCOPES - Truy vấn nhanh
	// ============================================

	/**
	 * Lọc ghế còn trống
	 */
	public function scopeAvailable($query)
	{
		return $query->where('Status', 'Available');
	}

	/**
	 * Lọc ghế VIP
	 */
	public function scopeVip($query)
	{
		return $query->where('SeatType', 'VIP');
	}

	/**
	 * Lọc ghế Couple
	 */
	public function scopeCouple($query)
	{
		return $query->where('SeatType', 'Couple');
	}

	/**
	 * Lọc ghế theo hàng
	 */
	public function scopeRow($query, $row)
	{
		return $query->where('Row', $row);
	}

	// ============================================
	// ACCESSORS - Lấy thông tin bổ sung
	// ============================================

	/**
	 * Lấy tên đầy đủ của ghế (VD: A1, B12)
	 */
	public function getFullNameAttribute()
	{
		return $this->Row . $this->Number;
	}

	/**
	 * Kiểm tra ghế có trống không
	 */
	public function getIsAvailableAttribute()
	{
		return $this->Status === 'Available';
	}

	/**
	 * Kiểm tra ghế có đang được đặt không
	 */
	public function getIsBookedAttribute()
	{
		return $this->Status === 'Booked';
	}

	// ============================================
	// FUNCTIONS
	// ============================================

	/**
	 * Kiểm tra ghế có trống trong suất chiếu không
	 */
	public function isAvailableForShowtime($showtimeId)
	{
		$showtimeSeat = $this->showtimeSeats()
			->where('ShowtimeId', $showtimeId)
			->first();
		
		if (!$showtimeSeat) {
			return true; // Chưa có trạng thái cho suất chiếu này
		}
		
		return $showtimeSeat->Status === 'Available';
	}

	/**
	 * Lấy trạng thái ghế trong suất chiếu
	 */
	public function getStatusForShowtime($showtimeId)
	{
		$showtimeSeat = $this->showtimeSeats()
			->where('ShowtimeId', $showtimeId)
			->first();
		
		return $showtimeSeat ? $showtimeSeat->Status : 'Available';
	}
}