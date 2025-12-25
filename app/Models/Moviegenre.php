<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Moviegenre
 * 
 * @property int $MovieGenreId
 * @property int $MovieId
 * @property int $GenreId
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property Movie $movie
 * @property Genre $genre
 * @property User|null $user
 *
 * @package App\Models
 */
class Moviegenre extends Model
{
	protected $table = 'moviegenres';
	protected $primaryKey = 'MovieGenreId';
	public $timestamps = false;

	protected $casts = [
		'MovieId' => 'int',
		'GenreId' => 'int',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'MovieId',
		'GenreId',
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

	public function genre()
	{
		return $this->belongsTo(Genre::class, 'GenreId');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}
}
