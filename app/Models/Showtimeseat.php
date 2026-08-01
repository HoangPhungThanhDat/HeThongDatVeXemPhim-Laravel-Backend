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
 *
 * @package App\Models
 */
class Showtimeseat extends Model
{
    protected $table = 'showtimeseats';
    protected $primaryKey = 'ShowtimeSeatId';
    public $incrementing = true;  // ✅ INT auto-increment
    protected $keyType = 'int';   // ✅ INT
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

    /**
     * Quan hệ với Showtime
     */
    public function showtime()
    {
        return $this->belongsTo(Showtime::class, 'ShowtimeId', 'ShowtimeId');
    }

    /**
     * Quan hệ với Seat
     */
    public function seat()
    {
        return $this->belongsTo(Seat::class, 'SeatId', 'SeatId');
    }

    /**
     * Quan hệ với Ticket (thông qua SeatId và ShowtimeId)
     * Lấy vé đã đặt cho ghế này trong suất chiếu này
     */
    public function ticket()
    {
        return $this->hasOne(Ticket::class, 'SeatId', 'SeatId')
            ->where('ShowtimeId', $this->ShowtimeId)
            ->whereIn('Status', ['Paid', 'Pending']);
    }

    /**
     * Quan hệ với User thông qua Ticket (người đặt vé)
     */
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            Ticket::class,
            'SeatId',      // Foreign key on Ticket table
            'UserId',      // Foreign key on User table
            'SeatId',      // Local key on ShowtimeSeat table
            'UserId'       // Local key on Ticket table
        )->where('Ticket.ShowtimeId', $this->ShowtimeId);
    }
}