<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Ticket
 * 
 * @property int $TicketId
 * @property int $ShowtimeId
 * @property int $SeatId
 * @property int|null $UserId
 * @property Carbon $BookingTime
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property Showtime $showtime
 * @property Seat $seat
 * @property User|null $user
 * @property Collection|Orderdetail[] $orderdetails
 * @property Collection|Payment[] $payments
 *
 * @package App\Models
 */
class Ticket extends Model
{
	protected $table = 'tickets';
	protected $primaryKey = 'TicketId';
	public $timestamps = false;

	protected $casts = [
		'ShowtimeId' => 'int',
		'SeatId' => 'int',
		'UserId' => 'int',
		'BookingTime' => 'datetime',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'ShowtimeId',
		'SeatId',
		'UserId',
		'BookingTime',
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

	public function orderdetails()
	{
		return $this->hasMany(Orderdetail::class, 'TicketId');
	}

	public function payments()
	{
		return $this->hasMany(Payment::class, 'TicketId');
	}
}
