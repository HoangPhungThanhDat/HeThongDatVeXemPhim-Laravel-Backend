<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Distributor
 * 
 * @property int $DistributorId
 * @property string $Name
 * @property string|null $Country
 * @property string|null $Email
 * @property string|null $Phone
 * @property string|null $Website
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * @property int|null $MovieId
 * 
 * @property User|null $user
 * @property Movie|null $movie

 *
 * @package App\Models
 */
class Distributor extends Model
{
	protected $table = 'distributors';
	protected $primaryKey = 'DistributorId';
	public $timestamps = false;

	protected $casts = [
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int',
		 'MovieId' => 'int'
	];

	protected $fillable = [
		'Name',
		 'MovieId',
		'Country',
		'Email',
		'Phone',
		'Website',
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
	public function movie()
    {
        return $this->belongsTo(Movie::class, 'MovieId');
    }
}