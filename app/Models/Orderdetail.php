<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Orderdetail
 * 
 * @property int $OrderDetailId
 * @property int $OrderId
 * @property int|null $TicketId
 * @property int|null $ItemId
 * @property int $Quantity
 * @property float $Price
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property Order $order
 * @property Ticket|null $ticket
 * @property Foodanddrink|null $foodanddrink
 * @property User|null $user
 *
 * @package App\Models
 */
class Orderdetail extends Model
{
	protected $table = 'orderdetails';
	protected $primaryKey = 'OrderDetailId';
	public $timestamps = false;

	protected $casts = [
		'OrderId' => 'int',
		'TicketId' => 'int',
		'ItemId' => 'int',
		'Quantity' => 'int',
		'Price' => 'float',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'OrderId',
		'TicketId',
		'ItemId',
		'Quantity',
		'Price',
		'Status',
		'CreatedAt',
		'UpdatedAt',
		'CreatedBy',
		'UpdatedBy'
	];

	public function order()
	{
		return $this->belongsTo(Order::class, 'OrderId');
	}

	public function ticket()
	{
		return $this->belongsTo(Ticket::class, 'TicketId');
	}

	public function foodanddrink()
	{
		return $this->belongsTo(Foodanddrink::class, 'ItemId');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}
}
