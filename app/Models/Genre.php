<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Genre
 * 
 * @property int $GenreId
 * @property string $Name
 * @property string|null $Description
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property User|null $user
 * @property Collection|Movie[] $movies
 *
 * @package App\Models
 */
class Genre extends Model
{
	protected $table = 'genres';
	protected $primaryKey = 'GenreId';
	public $timestamps = false;

	protected $casts = [
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'Name',
		'Description',
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

	public function movies()
	{
		return $this->belongsToMany(Movie::class, 'moviegenres', 'GenreId', 'MovieId')
					->withPivot('MovieGenreId', 'Status', 'CreatedAt', 'UpdatedAt', 'CreatedBy', 'UpdatedBy');
	}
}
