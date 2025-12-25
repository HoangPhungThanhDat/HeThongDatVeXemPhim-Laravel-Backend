<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Payment
 * 
 * @property int $PaymentId
 * @property int|null $TicketId
 * @property float $Amount
 * @property string $PaymentMethod
 * @property string $PaymentStatus
 * @property Carbon|null $PaymentDate
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property Ticket|null $ticket
 * @property User|null $user
 *
 * @package App\Models
 */
class Payment extends Model
{
	protected $table = 'payments';
	protected $primaryKey = 'PaymentId';
	public $timestamps = false;

	protected $casts = [
		'TicketId' => 'int',
		'Amount' => 'float',
		'PaymentDate' => 'datetime',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'TicketId',
		'Amount',
		'PaymentMethod',
		'PaymentStatus',
		'PaymentDate',
		'Status',
		'CreatedAt',
		'UpdatedAt',
		'CreatedBy',
		'UpdatedBy'
	];

	// Auto set các giá trị thời gian khi tạo mới
    protected static function booted()
    {
        static::creating(function ($payment) {
            // Nếu chưa có ngày thanh toán -> gán hiện tại
            if (empty($payment->PaymentDate)) {
                $payment->PaymentDate = Carbon::now();
            }

            // Nếu chưa có ngày tạo -> gán hiện tại
            if (empty($payment->CreatedAt)) {
                $payment->CreatedAt = Carbon::now();
            }

            // Nếu chưa có ngày cập nhật -> gán hiện tại
            if (empty($payment->UpdatedAt)) {
                $payment->UpdatedAt = Carbon::now();
            }
        });

        static::updating(function ($payment) {
            // Mỗi khi update -> luôn cập nhật UpdatedAt
            $payment->UpdatedAt = Carbon::now();
        });
    }

	public function ticket()
	{
		return $this->belongsTo(Ticket::class, 'TicketId');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}
}
