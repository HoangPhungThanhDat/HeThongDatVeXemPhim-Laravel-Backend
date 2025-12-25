<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Membership
 * 
 * @property int $MembershipId
 * @property int $UserId
 * @property string $Level
 * @property int $Points
 * @property Carbon $StartDate
 * @property Carbon|null $EndDate
 * @property string|null $Benefits
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
class Membership extends Model
{
	protected $table = 'memberships';
	protected $primaryKey = 'MembershipId';
	public $timestamps = false;

	protected $casts = [
		'UserId' => 'int',
		'Points' => 'int',
		'StartDate' => 'datetime',
		'EndDate' => 'datetime',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'UserId',
		'Level',
		'Points',
		'StartDate',
		'EndDate',
		'Benefits',
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
