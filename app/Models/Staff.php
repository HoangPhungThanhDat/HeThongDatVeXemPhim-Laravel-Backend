<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Staff
 * 
 * @property int $StaffId
 * @property int $CinemaId
 * @property string $FullName
 * @property string $Email
 * @property string|null $Phone
 * @property string|null $Position
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property Cinema $cinema
 * @property User|null $user
 *
 * @package App\Models
 */
class Staff extends Model
{
	protected $table = 'staff';
	protected $primaryKey = 'StaffId';
	public $timestamps = false;

	protected $casts = [
		'CinemaId' => 'int',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'CinemaId',
		'FullName',
		'Email',
		'Phone',
		'Position',
		'Status',
		'CreatedAt',
		'UpdatedAt',
		'CreatedBy',
		'UpdatedBy'
	];

	public function cinema()
	{
		return $this->belongsTo(Cinema::class, 'CinemaId');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}
}
