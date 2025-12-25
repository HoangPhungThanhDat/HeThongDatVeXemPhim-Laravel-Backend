<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Promotion
 * 
 * @property int $PromotionId
 * @property string $Title
 * @property string|null $Code
 * @property string|null $Description
 * @property string|null $ImageUrl
 * @property string $DiscountType
 * @property float $DiscountValue
 * @property Carbon|null $StartDate
 * @property Carbon|null $EndDate
 * @property bool $IsActive
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property User|null $user
 * @property Collection|Order[] $orders
 *
 * @package App\Models
 */
class Promotion extends Model
{
	protected $table = 'promotions';
	protected $primaryKey = 'PromotionId';
	public $timestamps = false;

	protected $casts = [
		'DiscountValue' => 'float',
		'StartDate' => 'datetime',
		'EndDate' => 'datetime',
		'IsActive' => 'bool',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'Title',
		'Code',
		'Description',
		'ImageUrl',
		'DiscountType',
		'DiscountValue',
		'StartDate',
		'EndDate',
		'IsActive',
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

	public function orders()
	{
		return $this->hasMany(Order::class, 'PromotionId');
	}
}
