<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Order
 * 
 * @property int $OrderId
 * @property int|null $UserId
 * @property Carbon $OrderDate
 * @property float $TotalAmount
 * @property int|null $PromotionId
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property User|null $user
 * @property Promotion|null $promotion
 * @property Collection|Orderdetail[] $orderdetails
 *
 * @package App\Models
 */
class Order extends Model
{
	protected $table = 'orders';
	protected $primaryKey = 'OrderId';
	public $timestamps = false;

	protected $casts = [
		'UserId' => 'int',
		'OrderDate' => 'datetime',
		'TotalAmount' => 'float',
		'PromotionId' => 'int',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'UserId',
		'OrderDate',
		'TotalAmount',
		'PromotionId',
		'Status',
		'CreatedAt',
		'UpdatedAt',
		'CreatedBy',
		'UpdatedBy'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}

	public function promotion()
	{
		return $this->belongsTo(Promotion::class, 'PromotionId');
	}

	public function orderdetails()
	{
		return $this->hasMany(Orderdetail::class, 'OrderId');
	}
}
