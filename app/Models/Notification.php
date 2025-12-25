<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Notification
 * 
 * @property int $NotificationId
 * @property int|null $UserId
 * @property string $Title
 * @property string $Message
 * @property string $Type
 * @property bool $IsRead
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
class Notification extends Model
{
	protected $table = 'notifications';
	protected $primaryKey = 'NotificationId';
	public $timestamps = false;

	protected $casts = [
		'UserId' => 'int',
		'IsRead' => 'bool',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'UserId',
		'Title',
		'Message',
		'Type',
		'IsRead',
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
