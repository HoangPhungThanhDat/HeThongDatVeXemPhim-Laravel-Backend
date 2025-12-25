<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Loginhistory
 * 
 * @property int $LoginId
 * @property int|null $UserId
 * @property Carbon $LoginTime
 * @property string|null $IpAddress
 * @property string|null $DeviceInfo
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property User|null $user
 *
 * @package App\Models
 */
class Loginhistory extends Model
{
	protected $table = 'loginhistory';
	protected $primaryKey = 'LoginId';
	public $timestamps = false;

	protected $casts = [
		'UserId' => 'int',
		'LoginTime' => 'datetime',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'UserId',
		'LoginTime',
		'IpAddress',
		'DeviceInfo',
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
}
