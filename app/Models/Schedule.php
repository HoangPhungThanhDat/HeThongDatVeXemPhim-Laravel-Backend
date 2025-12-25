<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Schedule
 * 
 * @property int $ScheduleId
 * @property int $MovieId
 * @property int $RoomId
 * @property Carbon $StartDate
 * @property Carbon $EndDate
 * @property string|null $DaysOfWeek
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
 *
 * @package App\Models
 */
class Schedule extends Model
{
	protected $table = 'schedules';
	protected $primaryKey = 'ScheduleId';
	public $timestamps = false;

	protected $casts = [
		'MovieId' => 'int',
		'RoomId' => 'int',
		'StartDate' => 'datetime',
		'EndDate' => 'datetime',
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
		'StartDate',
		'EndDate',
		'DaysOfWeek',
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
}
