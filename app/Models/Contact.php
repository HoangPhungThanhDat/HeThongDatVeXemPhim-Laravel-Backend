<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Contact
 * 
 * @property int $ContactId
 * @property int|null $UserId
 * @property string $FullName
 * @property string $Email
 * @property string|null $Phone
 * @property string $Message
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
class Contact extends Model
{
	protected $table = 'contacts';
	protected $primaryKey = 'ContactId';
	public $timestamps = false;

	protected $casts = [
		'UserId' => 'int',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'UserId',
		'FullName',
		'Email',
		'Phone',
		'Message',
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
