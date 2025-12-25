<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Room
 * 
 * @property int $RoomId
 * @property int $CinemaId
 * @property string $Name
 * @property int $SeatCount
 * @property string|null $RoomType
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property Cinema $cinema
 * @property User|null $user
 * @property Collection|Schedule[] $schedules
 * @property Collection|Seat[] $seats
 * @property Collection|Showtime[] $showtimes
 *
 * @package App\Models
 */
class Room extends Model
{
	protected $table = 'rooms';
	protected $primaryKey = 'RoomId';
	public $timestamps = false;

	protected $casts = [
		'CinemaId' => 'int',
		'SeatCount' => 'int',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'CinemaId',
		'Name',
		'SeatCount',
		'RoomType',
		'Status',
		'CreatedAt',
		'UpdatedAt',
		'CreatedBy',
		'UpdatedBy'
	];

	public function cinema()
	{
		return $this->belongsTo(Cinema::class, 'CinemaId');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}

	public function schedules()
	{
		return $this->hasMany(Schedule::class, 'RoomId');
	}

	public function seats()
	{
		return $this->hasMany(Seat::class, 'RoomId');
	}

	public function showtimes()
	{
		return $this->hasMany(Showtime::class, 'RoomId');
	}
}
