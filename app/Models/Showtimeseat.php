<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Showtimeseat
 * 
 * @property int $ShowtimeSeatId
 * @property int $ShowtimeId
 * @property int $SeatId
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property Showtime $showtime
 * @property Seat $seat
 * @property User|null $user
 *
 * @package App\Models
 */
class Showtimeseat extends Model
{
	protected $table = 'showtimeseats';
	protected $primaryKey = 'ShowtimeSeatId';
	public $timestamps = false;

	protected $casts = [
		'ShowtimeId' => 'int',
		'SeatId' => 'int',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'ShowtimeId',
		'SeatId',
		'Status',
		'CreatedAt',
		'UpdatedAt',
		'CreatedBy',
		'UpdatedBy'
	];

	public function showtime()
	{
		return $this->belongsTo(Showtime::class, 'ShowtimeId');
	}

	public function seat()
	{
		return $this->belongsTo(Seat::class, 'SeatId');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}
}
