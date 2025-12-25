<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuditlogController;
use App\Http\Controllers\Api\BannerController;
use App\Http\Controllers\Api\CinemaController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DistributorController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\GenreController;
use App\Http\Controllers\Api\LoginhistoryController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\FoodanddrinkController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\NewsController;
use App\Http\Controllers\Api\RoomController;
use App\Http\Controllers\Api\MembershipController;
use App\Http\Controllers\Api\MovieController;
use App\Http\Controllers\Api\MoviecastController;
use App\Http\Controllers\Api\MoviegenreController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SeatController;
use App\Http\Controllers\Api\ShowtimeseatController;
use App\Http\Controllers\Api\OrderdetailController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\ShowtimeController;
use App\Http\Controllers\Api\StaffController;

// 🔓 Public routes (không cần token)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// 📌 Public GET routes cho auditlogs
Route::get('/auditlogs', [AuditlogController::class, 'index']);
Route::get('/auditlogs/{LogId}', [AuditlogController::class, 'show']);
// 📌 Public GET routes cho genres
Route::get('/genres',[GenreController::class, 'index']);
Route::get('/genres/{GenreId}',[GenreController::class, 'show']);
// 📌 Public GET routes cho banner
Route::get('/banners',[BannerController::class, 'index']);
Route::get('/banners/{BannerId}',[BannerController::class, 'show']);
// 📌 Public GET routes cho cinemas
Route::get('/cinemas',[CinemaController::class, 'index']);
Route::get('/cinemas/{CinemaId}',[CinemaController::class, 'show']);
// 📌 Public GET routes cho contact
Route::get('/contacts',[ContactController::class, 'index']);
Route::get('/contacts/{ContactId}',[ContactController::class, 'show']);
// 📌 Public GET routes cho distributor
Route::get('/distributors',[DistributorController::class, 'index']);
Route::get('/distributors/{distributorId}',[DistributorController::class, 'show']);
// 📌 Public GET routes cho Loginhistory
Route::get('/loginhistorys',[LoginhistoryController::class, 'index']);
Route::get('/loginhistorys/{LoginId}',[LoginhistoryController::class, 'show']);
//foodanddrinks
Route::get('/foodanddrinks', [FoodanddrinkController::class, 'index']);
Route::get('/foodanddrinks/{ItemId}', [FoodanddrinkController::class, 'show']);
//roles
Route::get('/roles', [RoleController::class, 'index']);
Route::get('/roles/{RoleId}', [RoleController::class, 'show']);
//users
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{UserId}', [UserController::class, 'show']);
//notifications
Route::get('/notifications', [NotificationController::class, 'index']);
Route::get('/notifications/{NotificationId}', [NotificationController::class, 'show']);
//orders
Route::get('/orders', [OrderController::class, 'index']);   
Route::get('/orders/{OrderId}', [OrderController::class, 'show']);
//News
Route::get('/news', [NewsController::class, 'index']);   
Route::get('/news/{NewsId}', [NewsController::class, 'show']);
//Room
Route::get('/rooms', [RoomController::class, 'index']);   
Route::get('/rooms/{RoomId}', [RoomController::class, 'show']);
//memberships
Route::get('/memberships', [MembershipController::class, 'index']);
Route::get('/memberships/{MembershipId}', [MembershipController::class, 'show']);
//movies
Route::get('/movies', [MovieController::class, 'index']);
Route::get('/movies/{MovieId}', [MovieController::class, 'show']);
//moviecasts
Route::get('/moviecasts', [MoviecastController::class, 'index']);
Route::get('/moviecasts/{CastId}', [MoviecastController::class, 'show']);
//moviegenres
Route::get('/moviegenres', [MoviegenreController::class, 'index']);
Route::get('/moviegenres/{MovieGenreId}', [MoviegenreController::class, 'show']);
//payments
Route::get('/payments', [PaymentController::class, 'index']);
Route::get('/payments/{PaymentId}', [PaymentController::class, 'show']);
// seats
Route::get('/seats', [SeatController::class, 'index']);
Route::get('/seats/{SeatId}', [SeatController::class, 'show']);
// showtimeseats
Route::get('/showtimeseats', [ShowtimeseatController::class, 'index']);
Route::get('/showtimeseats/{Id}', [ShowtimeseatController::class, 'show']);
//orderdetails
Route::get('/orderdetails', [OrderdetailController::class, 'index']);
Route::get('/orderdetails/{OrderDetailId}', [OrderdetailController::class, 'show']);
//promotions
Route::get('/promotions', [PromotionController::class, 'index']);
Route::get('/promotions/{PromotionId}', [PromotionController::class, 'show']);
//tickets
Route::get('/tickets', [TicketController::class, 'index']);
Route::get('/tickets/{TicketId}', [TicketController::class, 'show']);
//schedules
Route::get('/schedules', [ScheduleController::class, 'index']);
Route::get('/schedules/{ScheduleId}', [ScheduleController::class, 'show']);
//Wishlist
Route::get('/wishlists', [WishlistController::class, 'index']);
Route::get('/wishlists/{WishlistId}', [WishlistController::class, 'show']);
//reviews
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/{ReviewId}', [ReviewController::class, 'show']);
//showtimes
Route::get('/showtimes', [ShowtimeController::class, 'index']);
Route::get('/showtimes/{ShowtimeId}', [ShowtimeController::class, 'show']);
//staffs
Route::get('/staffs', [StaffController::class, 'index']);
Route::get('/staffs/{StaffId}', [StaffController::class, 'show']);
// 🔐 Protected routes (cần JWT token)
Route::middleware(['auth:api'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    // 📌 Chỉ Admin mới được phép thêm/sửa/xóa auditlogs
    Route::middleware(['checkrole:Admin'])->group(function () {
        Route::post('/auditlogs', [AuditlogController::class, 'store']);
        Route::put('/auditlogs/{LogId}', [AuditlogController::class, 'update']);
        Route::delete('/auditlogs/{LogId}', [AuditlogController::class, 'destroy']);
        
        Route::post('/genres', [GenreController::class, 'store']);
        Route::put('/genres/{GenreId}', [GenreController::class, 'update']);
        Route::delete('/genres/{GenreId}', [GenreController::class, 'destroy']);

        Route::post('/banners', [BannerController::class, 'store']);
        Route::put('/banners/{BannerId}', [BannerController::class, 'update']);
        Route::delete('/banners/{BannerId}', [BannerController::class, 'destroy']);

        Route::post('/cinemas', [CinemaController::class, 'store']);
        Route::put('/cinemas/{CinemaId}', [CinemaController::class, 'update']);
        Route::delete('/cinemas/{CinemaId}', [CinemaController::class, 'destroy']);

        Route::post('/contacts', [ContactController::class, 'store']);
        Route::put('/contacts/{ContactId}', [ContactController::class, 'update']);
        Route::delete('/contacts/{ContactId}', [ContactController::class, 'destroy']);

        Route::post('/distributors', [DistributorController::class, 'store']);
        Route::put('/distributors/{distributorId}', [DistributorController::class, 'update']);
        Route::delete('/distributors/{distributorId}', [DistributorController::class, 'destroy']);

        Route::post('/loginhistorys', [LoginhistoryController::class, 'store']);
        Route::put('/loginhistorys/{LoginId}', [LoginhistoryController::class, 'update']);
        Route::delete('/loginhistorys/{LoginId}', [LoginhistoryController::class, 'destroy']);

        //foodanddrinks
        Route::post('/foodanddrinks', [FoodanddrinkController::class, 'store']);
        Route::put('/foodanddrinks/{ItemId}', [FoodanddrinkController::class, 'update']);
        Route::delete('/foodanddrinks/{ItemId}', [FoodanddrinkController::class, 'destroy']);
        //roles
        Route::post('/roles', [RoleController::class, 'store']);
        Route::put('/roles/{RoleId}', [RoleController::class, 'update']);
        Route::delete('/roles/{RoleId}', [RoleController::class, 'destroy']);
        //users
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{UserId}', [UserController::class, 'update']);
        Route::delete('/users/{UserId}', [UserController::class, 'destroy']);
        //notifications
        Route::post('/notifications', [NotificationController::class, 'store']);
        Route::put('/notifications/{NotificationId}', [NotificationController::class, 'update']);
        Route::delete('/notifications/{NotificationId}', [NotificationController::class, 'destroy']);
        //orders
        Route::post('/orders', [OrderController::class, 'store']);  
        Route::put('/orders/{OrderId}', [OrderController::class, 'update']);
        Route::delete('/orders/{OrderId}', [OrderController::class, 'destroy']);
        //news
        Route::post('/news', [NewsController::class, 'store']);
        Route::put('/news/{NewsId}', [NewsController::class, 'update']);
        Route::delete('/news/{NewsId}', [NewsController::class, 'destroy']);
        //rooms
        Route::post('/rooms', [RoomController::class, 'store']);
        Route::put('/rooms/{RoomId}', [RoomController::class, 'update']);
        Route::delete('/rooms/{RoomId}', [RoomController::class, 'destroy']);
        //memberships
        Route::post('/memberships', [MembershipController::class, 'store']);
        Route::put('/memberships/{MembershipId}', [MembershipController::class, 'update']);
        Route::delete('/memberships/{MembershipId}', [MembershipController::class, 'destroy']);
        //movies
        Route::post('/movies', [MovieController::class, 'store']);
        Route::put('/movies/{MovieId}', [MovieController::class, 'update']);
        Route::delete('/movies/{MovieId}', [MovieController::class, 'destroy']);
        //moviecasts
        Route::post('/moviecasts', [MoviecastController::class, 'store']);
        Route::put('/moviecasts/{CastId}', [MoviecastController::class, 'update']);
        Route::delete('/moviecasts/{CastId}', [MoviecastController::class, 'destroy']);
        //moviegenres
        Route::post('/moviegenres', [MoviegenreController::class, 'store']);
        Route::put('/moviegenres/{MovieGenreId}', [MoviegenreController::class, 'update']);
        Route::delete('/moviegenres/{MovieGenreId}', [MoviegenreController::class, 'destroy']);
        //payments
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::put('/payments/{PaymentId}', [PaymentController::class, 'update']);
        Route::delete('/payments/{PaymentId}', [PaymentController::class, 'destroy']);
        //seats
        Route::post('/seats', [SeatController::class, 'store']);    
        Route::put('/seats/{SeatId}', [SeatController::class, 'update']);
        Route::delete('/seats/{SeatId}', [SeatController::class, 'destroy']);
        //showtimeseats
        Route::post('/showtimeseats', [ShowtimeseatController::class, 'store']);    
        Route::put('/showtimeseats/{Id}', [ShowtimeseatController::class, 'update']);
        Route::delete('/showtimeseats/{Id}', [ShowtimeseatController::class, 'destroy']);
        //orderdetails
        Route::post('/orderdetails', [OrderdetailController::class, 'store']);
        Route::put('/orderdetails/{OrderDetailId}', [OrderdetailController::class, 'update']);
        Route::delete('/orderdetails/{OrderDetailId}', [OrderdetailController::class, 'destroy']);
        //promotions
        Route::post('/promotions', [PromotionController::class, 'store']);
        Route::put('/promotions/{PromotionId}', [PromotionController::class, 'update']);
        Route::delete('/promotions/{PromotionId}', [PromotionController::class, 'destroy']);
        //tickets
        Route::post('/tickets', [TicketController::class, 'store']);
        Route::put('/tickets/{TicketId}', [TicketController::class, 'update']);
        Route::delete('/tickets/{TicketId}', [TicketController::class, 'destroy']);
        //schedules
        Route::post('/schedules', [ScheduleController::class, 'store']);
        Route::put('/schedules/{ScheduleId}', [ScheduleController::class, 'update']);
        Route::delete('/schedules/{ScheduleId}', [ScheduleController::class, 'destroy']);
        //wishlists
        Route::post('/wishlists', [WishlistController::class, 'store']);
        Route::put('/wishlists/{WishlistId}', [WishlistController::class, 'update']);
        Route::delete('/wishlists/{WishlistId}', [WishlistController::class, 'destroy']);
        //reviews
        Route::post('/reviews', [ReviewController::class, 'store']);
        Route::put('/reviews/{ReviewId}', [ReviewController::class, 'update']);
        Route::delete('/reviews/{ReviewId}', [ReviewController::class, 'destroy']);
        //showtimes
        Route::post('/showtimes', [ShowtimeController::class, 'store']);
        Route::put('/showtimes/{ShowtimeId}', [ShowtimeController::class, 'update']);
        Route::delete('/showtimes/{ShowtimeId}', [ShowtimeController::class, 'destroy']);
        //staffs
        Route::post('/staffs', [StaffController::class, 'store']);
        Route::put('/staffs/{StaffId}', [StaffController::class, 'update']);
        Route::delete('/staffs/{StaffId}', [StaffController::class, 'destroy']);
    });
});