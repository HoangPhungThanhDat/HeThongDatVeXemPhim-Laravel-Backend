<?php

/**
 * Created by Reliese Model.
 */


 namespace App\Models;

 use Carbon\Carbon;
 use Illuminate\Database\Eloquent\Collection;
 use Illuminate\Database\Eloquent\Model;
 use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;
 use Illuminate\Foundation\Auth\User as Authenticatable;
 use Illuminate\Notifications\Notifiable;
/**
 * Class User
 * 
 * @property int $UserId
 * @property string $FullName
 * @property string $Email
 * @property string $PasswordHash
 * @property string|null $PhoneNumber
 * @property string|null $Gender
 * @property Carbon|null $DateOfBirth
 * @property int|null $RoleId
 * @property string $Status
 * @property Carbon $CreatedAt
 * @property Carbon $UpdatedAt
 * @property int|null $CreatedBy
 * @property int|null $UpdatedBy
 * 
 * @property Role|null $role
 * @property User|null $user
 * @property Collection|Auditlog[] $auditlogs
 * @property Collection|Banner[] $banners
 * @property Collection|Cinema[] $cinemas
 * @property Collection|Contact[] $contacts
 * @property Collection|Distributor[] $distributors
 * @property Collection|Foodanddrink[] $foodanddrinks
 * @property Collection|Genre[] $genres
 * @property Collection|Loginhistory[] $loginhistories
 * @property Collection|Membership[] $memberships
 * @property Collection|Menu[] $menus
 * @property Collection|Moviecast[] $moviecasts
 * @property Collection|Moviegenre[] $moviegenres
 * @property Collection|Movie[] $movies
 * @property Collection|News[] $news
 * @property Collection|Notification[] $notifications
 * @property Collection|Orderdetail[] $orderdetails
 * @property Collection|Order[] $orders
 * @property Collection|Payment[] $payments
 * @property Collection|Promotion[] $promotions
 * @property Collection|Review[] $reviews
 * @property Collection|Room[] $rooms
 * @property Collection|Schedule[] $schedules
 * @property Collection|Seat[] $seats
 * @property Collection|Showtime[] $showtimes
 * @property Collection|Showtimeseat[] $showtimeseats
 * @property Collection|Staff[] $staff
 * @property Collection|Ticket[] $tickets
 * @property Collection|User[] $users
 * @property Collection|Wishlist[] $wishlists
 *
 * @package App\Models
 */
class User extends Authenticatable implements JWTSubject
{
	use Notifiable;

	protected $table = 'users';
	protected $primaryKey = 'UserId';
	public $timestamps = false;
	public $incrementing = true;
	 protected $keyType = 'int';

	protected $casts = [
		'DateOfBirth' => 'datetime',
		'RoleId' => 'int',
		'CreatedAt' => 'datetime',
		'UpdatedAt' => 'datetime',
		'CreatedBy' => 'int',
		'UpdatedBy' => 'int'
	];

	protected $fillable = [
		'FullName',
		'Email',
		'PasswordHash',
		'PhoneNumber',
		'Gender',
		'DateOfBirth',
		'RoleId',
		'Status',
		'CreatedAt',
		'UpdatedAt',
		'CreatedBy',
		'UpdatedBy'
	];
	//hàm jwt
	public function getJWTIdentifier()
	{
		return $this->getKey();
	}

	public function getJWTCustomClaims()
	{
		return ['role' => $this->role->RoleName ?? 'User'];
	}
	public function getAuthPassword()
	{
		return $this->PasswordHash;
	}

	public function role()
	{
		return $this->belongsTo(Role::class, 'RoleId');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'UpdatedBy');
	}

	public function auditlogs()
	{
		return $this->hasMany(Auditlog::class, 'UserId');
	}

	public function banners()
	{
		return $this->hasMany(Banner::class, 'UpdatedBy');
	}

	public function cinemas()
	{
		return $this->hasMany(Cinema::class, 'UpdatedBy');
	}

	public function contacts()
	{
		return $this->hasMany(Contact::class, 'UpdatedBy');
	}

	public function distributors()
	{
		return $this->hasMany(Distributor::class, 'UpdatedBy');
	}

	public function foodanddrinks()
	{
		return $this->hasMany(Foodanddrink::class, 'UpdatedBy');
	}

	public function genres()
	{
		return $this->hasMany(Genre::class, 'UpdatedBy');
	}

	public function loginhistories()
	{
		return $this->hasMany(Loginhistory::class, 'UpdatedBy');
	}

	public function memberships()
	{
		return $this->hasMany(Membership::class, 'UpdatedBy');
	}

	public function menus()
	{
		return $this->hasMany(Menu::class, 'UpdatedBy');
	}

	public function moviecasts()
	{
		return $this->hasMany(Moviecast::class, 'UpdatedBy');
	}

	public function moviegenres()
	{
		return $this->hasMany(Moviegenre::class, 'UpdatedBy');
	}

	public function movies()
	{
		return $this->hasMany(Movie::class, 'UpdatedBy');
	}

	public function news()
	{
		return $this->hasMany(News::class, 'UpdatedBy');
	}

	public function notifications()
	{
		return $this->hasMany(Notification::class, 'UpdatedBy');
	}

	public function orderdetails()
	{
		return $this->hasMany(Orderdetail::class, 'UpdatedBy');
	}

	public function orders()
	{
		return $this->hasMany(Order::class, 'UpdatedBy');
	}

	public function payments()
	{
		return $this->hasMany(Payment::class, 'UpdatedBy');
	}

	public function promotions()
	{
		return $this->hasMany(Promotion::class, 'UpdatedBy');
	}

	public function reviews()
	{
		return $this->hasMany(Review::class, 'UpdatedBy');
	}

	public function rooms()
	{
		return $this->hasMany(Room::class, 'UpdatedBy');
	}

	public function schedules()
	{
		return $this->hasMany(Schedule::class, 'UpdatedBy');
	}

	public function seats()
	{
		return $this->hasMany(Seat::class, 'UpdatedBy');
	}

	public function showtimes()
	{
		return $this->hasMany(Showtime::class, 'UpdatedBy');
	}

	public function showtimeseats()
	{
		return $this->hasMany(Showtimeseat::class, 'UpdatedBy');
	}

	public function staff()
	{
		return $this->hasMany(Staff::class, 'UpdatedBy');
	}

	public function tickets()
	{
		return $this->hasMany(Ticket::class, 'UpdatedBy');
	}

	public function users()
	{
		return $this->hasMany(User::class, 'UpdatedBy');
	}

	public function wishlists()
	{
		return $this->hasMany(Wishlist::class, 'UserId');
	}
}
