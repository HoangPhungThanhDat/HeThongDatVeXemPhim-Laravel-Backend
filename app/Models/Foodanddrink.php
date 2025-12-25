<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Foodanddrink
 * 
 * @property int $ItemId
 * @property string $Name
 * @property string|null $Description
 * @property float $Price
 * @property string|null $ImageUrl
 * @property bool $IsAvailable
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property User|null $user
 * @property Collection|Orderdetail[] $orderdetails
 *
 * @package App\Models
 */
class Foodanddrink extends Model
{
	protected $table = 'foodanddrinks';
	protected $primaryKey = 'ItemId';
	public $timestamps = false;

	protected $casts = [
		'Price' => 'float',
		'IsAvailable' => 'bool',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'Name',
		'Description',
		'Price',
		'ImageUrl',
		'IsAvailable',
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

	public function orderdetails()
	{
		return $this->hasMany(Orderdetail::class, 'ItemId');
	}
}
