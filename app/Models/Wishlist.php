<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Wishlist
 * 
 * @property int $WishlistId
 * @property int $UserId
 * @property int $MovieId
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property string $Status
 * 
 * @property User $user
 * @property Movie $movie
 *
 * @package App\Models
 */
class Wishlist extends Model
{
	protected $table = 'wishlists';
	protected $primaryKey = 'WishlistId';
	public $timestamps = false;

	protected $casts = [
		'UserId' => 'int',
		'MovieId' => 'int',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime'
	];

	protected $fillable = [
		'UserId',
		'MovieId',
		'CreatedAt',
		'UpdatedAt',
		'Status'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'UserId');
	}

	public function movie()
	{
		return $this->belongsTo(Movie::class, 'MovieId');
	}
}
