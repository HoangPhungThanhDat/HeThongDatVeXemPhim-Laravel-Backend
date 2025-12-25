<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Cinema
 * 
 * @property int $CinemaId
 * @property string $Name
 * @property string|null $Address
 * @property string|null $City
 * @property string|null $Phone
 * @property string|null $Email
 * @property string|null $ImageUrl
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property User|null $user
 * @property Collection|Room[] $rooms
 * @property Collection|Staff[] $staff
 *
 * @package App\Models
 */
class Cinema extends Model
{
	protected $table = 'cinemas';
	protected $primaryKey = 'CinemaId';
	public $timestamps = false;

	protected $casts = [
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'Name',
		'Address',
		'City',
		'Phone',
		'Email',
		'ImageUrl',
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

	public function rooms()
	{
		return $this->hasMany(Room::class, 'CinemaId');
	}

	public function staff()
	{
		return $this->hasMany(Staff::class, 'CinemaId');
	}
}
