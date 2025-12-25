<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Review
 * 
 * @property int $ReviewId
 * @property int|null $UserId
 * @property int $MovieId
 * @property int $Rating
 * @property string|null $Comment
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * @property string $Status
 * 
 * @property User|null $user
 * @property Movie $movie
 *
 * @package App\Models
 */
class Review extends Model
{
	protected $table = 'reviews';
	protected $primaryKey = 'ReviewId';
	public $timestamps = false;

	protected $casts = [
		'UserId' => 'int',
		'MovieId' => 'int',
		'Rating' => 'int',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'UserId',
		'MovieId',
		'Rating',
		'Comment',
		'CreatedAt',
		'UpdatedAt',
		'CreatedBy',
		'UpdatedBy',
		'Status'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}

	public function movie()
	{
		return $this->belongsTo(Movie::class, 'MovieId');
	}
}
