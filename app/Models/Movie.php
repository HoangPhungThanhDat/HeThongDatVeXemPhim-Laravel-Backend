<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class Movie
 * 
 * @property int $MovieId
 * @property string $Title
 * @property string $Slug
 * @property string|null $Description
 * @property string|null $Genre
 * @property int|null $Duration
 * @property Carbon|null $ReleaseDate
 * @property string|null $PosterUrl
 * @property string|null $TrailerUrl
 * @property string|null $Language
 * @property string|null $Rated
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property User|null $user
 * @property Collection|Moviecast[] $moviecasts
 * @property Collection|Genre[] $genres
 * @property Collection|Review[] $reviews
 * @property Collection|Schedule[] $schedules
 * @property Collection|Showtime[] $showtimes
 * @property Collection|Wishlist[] $wishlists
 *
 * @package App\Models
 */
class Movie extends Model
{
	protected $table = 'movies';
	protected $primaryKey = 'MovieId';
	public $incrementing = true;
	protected $keyType = 'int';
	public $timestamps = false;

	protected $casts = [
		'MovieId' => 'int',
		'GenreId' => 'int',
		'Duration' => 'int',
		'ReleaseDate' => 'datetime',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'Title',
		'Slug',
		'Description',
		'GenreId',
		'Duration',
		'ReleaseDate',
		'PosterUrl',
		'TrailerUrl',
		'Language',
		'Rated',
		'Status',
		'CreatedAt',
		'UpdatedAt',
		'CreatedBy',
		'UpdatedBy'
	];
	protected static function boot()
	{
		parent::boot();

		static::creating(function ($movie) {
		  if (empty($movie->Slug) && !empty($movie->Title)) {
                $movie->Slug = Str::slug($movie->Title);
            }
        });

		 static::updating(function ($movie) {
            if (empty($movie->Slug) && !empty($movie->Title)) {
                $movie->Slug = Str::slug($movie->Title);
            }
        });
	}
		
	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}

	public function moviecasts()
	{
		return $this->hasMany(Moviecast::class, 'MovieId');
	}

	public function genre()
	{
		return $this->belongsTo(Genre::class, 'GenreId', 'GenreId');
	}

	public function reviews()
	{
		return $this->hasMany(Review::class, 'MovieId');
	}

	public function schedules()
	{
		return $this->hasMany(Schedule::class, 'MovieId');
	}

	public function showtimes()
	{
		return $this->hasMany(Showtime::class, 'MovieId');
	}

	public function wishlists()
	{
		return $this->hasMany(Wishlist::class, 'MovieId');
	}
}
