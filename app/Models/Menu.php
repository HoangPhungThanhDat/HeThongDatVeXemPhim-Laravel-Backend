<?php

/**
 * Created by Reliese Model.
 */

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Menu
 * 
 * @property int $MenuId
 * @property int|null $UserId
 * @property int|null $ParentId
 * @property string $Title
 * @property string $Slug
 * @property string|null $LinkUrl
 * @property int $OrderIndex
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property User|null $user
 * @property Menu|null $menu
 * @property Collection|Menu[] $menus
 *
 * @package App\Models
 */
class Menu extends Model
{
	protected $table = 'menus';
	protected $primaryKey = 'MenuId';
	public $timestamps = false;

	protected $casts = [
		'UserId' => 'int',
		'ParentId' => 'int',
		'OrderIndex' => 'int',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'UserId',
		'ParentId',
		'Title',
		'Slug',
		'LinkUrl',
		'OrderIndex',
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

	public function menu()
	{
		return $this->belongsTo(Menu::class, 'ParentId');
	}

	public function menus()
	{
		return $this->hasMany(Menu::class, 'ParentId');
	}
}
