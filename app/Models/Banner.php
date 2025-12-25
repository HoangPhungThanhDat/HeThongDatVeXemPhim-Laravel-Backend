<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Banner
 * 
 * @property int $BannerId
 * @property int|null $UserId
 * @property string $Title
 * @property string|null $ImageUrl
 * @property string|null $LinkUrl
 * @property string $Position
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
class Banner extends Model
{
	protected $table = 'banners';
	protected $primaryKey = 'BannerId';
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
		'Title',
		'ImageUrl',
		'LinkUrl',
		'Position',
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
