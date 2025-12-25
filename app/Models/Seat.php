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

	public function room()
	{
		return $this->belongsTo(Room::class, 'RoomId');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}

	public function showtimes()
	{
		return $this->belongsToMany(Showtime::class, 'showtimeseats', 'SeatId', 'ShowtimeId')
					->withPivot('ShowtimeSeatId', 'Status', 'CreatedAt', 'UpdatedAt', 'CreatedBy', 'UpdatedBy');
	}

	public function tickets()
	{
		return $this->hasMany(Ticket::class, 'SeatId');
	}
}
