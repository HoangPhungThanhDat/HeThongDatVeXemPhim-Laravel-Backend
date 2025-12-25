<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Moviecast
 * 
 * @property int $CastId
 * @property int $MovieId
 * @property string $Name
 * @property string $Role
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property Movie $movie
 * @property User|null $user
 *
 * @package App\Models
 */
class Moviecast extends Model
{
	protected $table = 'moviecast';
	protected $primaryKey = 'CastId';
	public $timestamps = false;

	protected $casts = [
		'MovieId' => 'int',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'MovieId',
		'Name',
		'Role',
		'Status',
		'CreatedAt',
		'UpdatedAt',
		'CreatedBy',
		'UpdatedBy'
	];

	public function movie()
	{
		return $this->belongsTo(Movie::class, 'MovieId');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}
}
