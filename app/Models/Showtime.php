<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Showtime
 * 
 * @property int $ShowtimeId
 * @property int $MovieId
 * @property int $RoomId
 * @property Carbon $StartTime
 * @property Carbon $EndTime
 * @property float $Price
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property Movie $movie
 * @property Room $room
 * @property User|null $user
 * @property Collection|Seat[] $seats
 * @property Collection|Ticket[] $tickets
 *
 * @package App\Models
 */
class Showtime extends Model
{
	protected $table = 'showtimes';
	protected $primaryKey = 'ShowtimeId';
	public $timestamps = false;

	protected $casts = [
		'MovieId' => 'int',
		'RoomId' => 'int',
		'StartTime' => 'datetime',
		'EndTime' => 'datetime',
		'Price' => 'float',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'MovieId',
		'RoomId',
		'StartTime',
		'EndTime',
		'Price',
		'Status',
		'CreatedAt',
		'UpdatedAt',
		'CreatedBy',
		'UpdatedBy'
	];

	public function movie()
	{
		return $this->belongsTo(Movie::class, 'MovieId');
	}

	public function room()
	{
		return $this->belongsTo(Room::class, 'RoomId');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}

	public function seats()
	{
		return $this->belongsToMany(Seat::class, 'showtimeseats', 'ShowtimeId', 'SeatId')
					->withPivot('ShowtimeSeatId', 'Status', 'CreatedAt', 'UpdatedAt', 'CreatedBy', 'UpdatedBy');
	}

	public function tickets()
	{
		return $this->hasMany(Ticket::class, 'ShowtimeId');
	}
}
