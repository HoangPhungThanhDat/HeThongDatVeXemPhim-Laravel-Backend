<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Auditlog
 * 
 * @property int $LogId
 * @property int|null $UserId
 * @property string $Action
 * @property string|null $Description
 * @property string|null $IpAddress
 * @property string|null $DeviceInfo
 * @property Carbon $CreatedAt
 * 
 * @property User|null $user
 *
 * @package App\Models
 */
class Auditlog extends Model
{
	protected $table = 'auditlogs';
	protected $primaryKey = 'LogId';
	public $timestamps = false;

	protected $casts = [
		'UserId' => 'int',
		'CreatedAt' => 'datetime'
	];

	protected $fillable = [
		'UserId',
		'Action',
		'Description',
		'IpAddress',
		'DeviceInfo',
		'CreatedAt'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'UserId');
	}
}
