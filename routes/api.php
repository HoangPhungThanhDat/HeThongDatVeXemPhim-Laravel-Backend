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

// ========================================================
// 🔓 PUBLIC ROUTES (Không cần token)
// ========================================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ==================== MOVIES ====================
// ⚠️ Routes cụ thể phải đặt TRƯỚC routes có {id}
Route::get('/movies/now-showing/all',              [MovieController::class, 'getNowShowing']);
Route::get('/movies/now-showing/filters',          [MovieController::class, 'getNowShowingFilters']);
Route::post('/movies/now-showing/filter',          [MovieController::class, 'filterNowShowing']);
Route::get('/movies/now-showing/search',           [MovieController::class, 'searchNowShowing']);
Route::get('/movies/now-showing/genre/{genreId}',  [MovieController::class, 'getNowShowingByGenre']);
Route::get('/movies/coming-soon',                  [MovieController::class, 'getComingSoon']);
Route::get('/movies/hot',                          [MovieController::class, 'getHotMovies']);
Route::get('/movies/latest',                       [MovieController::class, 'getLatestMovies']);
Route::get('/movies/statistics/overview',          [MovieController::class, 'getStatistics']);
Route::get('/movies/by-status/{status}',           [MovieController::class, 'getByStatus']);
Route::get('/movies/{id}/detail',                  [MovieController::class, 'getDetail']);
Route::get('/movies',                              [MovieController::class, 'index']);
Route::get('/movies/paged',                        [MovieController::class, 'getPaged']);  // ✅ PUBLIC + trước {MovieId}
Route::get('/movies/{MovieId}',                    [MovieController::class, 'show']);
Route::get('/moviecasts/paged',                    [MoviecastController::class, 'getPaged']);
Route::get('/showtimes/paged',                     [ShowtimeController::class, 'getPaged']);
// ==================== GENRES ====================
Route::get('/genres',           [GenreController::class, 'index']);
Route::get('/genres/{GenreId}', [GenreController::class, 'show']);

// ==================== AUDITLOGS ====================
Route::get('/auditlogs',          [AuditlogController::class, 'index']);
Route::get('/auditlogs/{LogId}',  [AuditlogController::class, 'show']);

// ==================== BANNERS ====================
Route::get('/banners',            [BannerController::class, 'index']);
Route::get('/banners/{BannerId}', [BannerController::class, 'show']);

// ==================== CINEMAS ====================
Route::get('/cinemas',                      [CinemaController::class, 'index']);
Route::get('/cinemas/{CinemaId}',           [CinemaController::class, 'show']);
Route::get('/cinemas/{cinemaId}/showtimes', [CinemaController::class, 'showtimes']);
Route::get('/movies/{movieId}/cinemas', [CinemaController::class, 'cinemasByMovie']);
Route::get('/movies/{movieId}/showtimes', [CinemaController::class, 'showtimesByMovie']);

// ==================== CONTACTS ====================
Route::get('/contacts',             [ContactController::class, 'index']);
Route::get('/contacts/{ContactId}', [ContactController::class, 'show']);
Route::post('/contacts',            [ContactController::class, 'store']);

// ==================== DISTRIBUTORS ====================
Route::get('/distributors',                 [DistributorController::class, 'index']);
Route::get('/distributors/{distributorId}', [DistributorController::class, 'show']);

// ==================== LOGIN HISTORY ====================
Route::get('/loginhistorys',           [LoginhistoryController::class, 'index']);
Route::get('/loginhistorys/{LoginId}', [LoginhistoryController::class, 'show']);

// ==================== FOOD & DRINKS ====================
Route::get('/foodanddrinks',          [FoodanddrinkController::class, 'index']);
Route::get('/foodanddrinks/{ItemId}', [FoodanddrinkController::class, 'show']);

// ==================== ROLES ====================
Route::get('/roles',          [RoleController::class, 'index']);
Route::get('/roles/{RoleId}', [RoleController::class, 'show']);

// ==================== USERS ====================
Route::get('/users',          [UserController::class, 'index']);
Route::get('/users/paged',    [UserController::class, 'getPaged']);  // ✅ trước {UserId}
Route::get('/users/{UserId}', [UserController::class, 'show']);

// ==================== NOTIFICATIONS ====================
Route::get('/notifications',                  [NotificationController::class, 'index']);
Route::get('/notifications/{NotificationId}', [NotificationController::class, 'show']);

// ==================== ORDERS ====================
Route::get('/orders',           [OrderController::class, 'index']);
Route::get('/orders/{OrderId}', [OrderController::class, 'show']);

// ==================== NEWS ====================
Route::get('/news',          [NewsController::class, 'index']);
Route::get('/news/{NewsId}', [NewsController::class, 'show']);

// ==================== ROOMS ====================
Route::get('/rooms',          [RoomController::class, 'index']);
Route::get('/rooms/{RoomId}', [RoomController::class, 'show']);

// ==================== MEMBERSHIPS ====================
Route::get('/memberships',                [MembershipController::class, 'index']);
Route::get('/memberships/{MembershipId}', [MembershipController::class, 'show']);

// ==================== MOVIE CASTS ====================
Route::get('/moviecasts',          [MoviecastController::class, 'index']);
Route::get('/moviecasts/{CastId}', [MoviecastController::class, 'show']);

// ==================== MOVIE GENRES ====================
Route::get('/moviegenres',                [MoviegenreController::class, 'index']);
Route::get('/moviegenres/{MovieGenreId}', [MoviegenreController::class, 'show']);

// ==================== PAYMENTS ====================
Route::get('/payments',             [PaymentController::class, 'index']);
Route::get('/payments/{PaymentId}', [PaymentController::class, 'show']);

// ==================== SEATS ====================
Route::get('/seats',          [SeatController::class, 'index']);
Route::get('/seats/{SeatId}', [SeatController::class, 'show']);

// ==================== SHOWTIME SEATS ====================
Route::get('/showtimeseats',       [ShowtimeseatController::class, 'index']);
Route::get('/showtimeseats/paged', [ShowtimeseatController::class, 'getPaged']); // ✅ trước {Id}
Route::get('/showtimeseats/{Id}',  [ShowtimeseatController::class, 'show']);

// ==================== ORDER DETAILS ====================
Route::get('/orderdetails',                 [OrderdetailController::class, 'index']);
Route::get('/orderdetails/{OrderDetailId}', [OrderdetailController::class, 'show']);

// ==================== PROMOTIONS ====================
Route::get('/promotions',               [PromotionController::class, 'index']);
Route::get('/promotions/paged',         [PromotionController::class, 'getPaged']); // ✅ trước {PromotionId}
Route::get('/promotions/{PromotionId}', [PromotionController::class, 'show']);

// ==================== TICKETS ====================
Route::get('/tickets',            [TicketController::class, 'index']);
Route::get('/tickets/{TicketId}', [TicketController::class, 'show']);

// ==================== SCHEDULES ====================
Route::get('/schedules',              [ScheduleController::class, 'index']);
Route::get('/schedules/{ScheduleId}', [ScheduleController::class, 'show']);

// ==================== WISHLISTS ====================
Route::get('/wishlists',              [WishlistController::class, 'index']);
Route::get('/wishlists/{WishlistId}', [WishlistController::class, 'show']);

// ==================== REVIEWS ====================
Route::get('/reviews',            [ReviewController::class, 'index']);
Route::get('/reviews/{ReviewId}', [ReviewController::class, 'show']);

// ==================== SHOWTIMES ====================
Route::get('/showtimes',                              [ShowtimeController::class,     'index']);
Route::get('/showtimes/{ShowtimeId}',                 [ShowtimeController::class,     'show']);
Route::get('/showtimes/{showtimeId}/seats',           [ShowtimeseatController::class, 'getSeatsByShowtime']);
Route::post('/showtimes/{showtimeId}/generate-seats', [ShowtimeseatController::class, 'generateSeats']);

// ==================== STAFFS ====================
Route::get('/staffs',           [StaffController::class, 'index']);
Route::get('/staffs/{StaffId}', [StaffController::class, 'show']);


// ========================================================
// 🔐 PROTECTED ROUTES (Cần JWT token)
// ========================================================

Route::middleware(['auth:api'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile',                [UserController::class, 'getProfile']);
    Route::put('/profile',                [UserController::class, 'updateProfile']);
    Route::put('/users/{UserId}/profile', [UserController::class, 'updateProfile']);
    Route::put('/change-password',        [UserController::class, 'changePassword']);

    // ==================== ADMIN ONLY ====================
    Route::middleware(['checkrole:Admin'])->group(function () {

        // Auditlogs
        Route::post('/auditlogs',           [AuditlogController::class, 'store']);
        Route::put('/auditlogs/{LogId}',    [AuditlogController::class, 'update']);
        Route::delete('/auditlogs/{LogId}', [AuditlogController::class, 'destroy']);

        // Genres
        Route::post('/genres',             [GenreController::class, 'store']);
        Route::put('/genres/{GenreId}',    [GenreController::class, 'update']);
        Route::delete('/genres/{GenreId}', [GenreController::class, 'destroy']);

        // Banners
        Route::post('/banners',             [BannerController::class, 'store']);
        Route::put('/banners/{BannerId}',   [BannerController::class, 'update']);
        Route::delete('/banners/{BannerId}',[BannerController::class, 'destroy']);

        // Cinemas
        Route::post('/cinemas',             [CinemaController::class, 'store']);
        Route::put('/cinemas/{CinemaId}',   [CinemaController::class, 'update']);
        Route::delete('/cinemas/{CinemaId}',[CinemaController::class, 'destroy']);

        // Contacts
        Route::put('/contacts/{ContactId}',    [ContactController::class, 'update']);
        Route::delete('/contacts/{ContactId}', [ContactController::class, 'destroy']);

        // Distributors
        Route::post('/distributors',                  [DistributorController::class, 'store']);
        Route::put('/distributors/{distributorId}',   [DistributorController::class, 'update']);
        Route::delete('/distributors/{distributorId}',[DistributorController::class, 'destroy']);

        // Login History
        Route::post('/loginhistorys',            [LoginhistoryController::class, 'store']);
        Route::put('/loginhistorys/{LoginId}',   [LoginhistoryController::class, 'update']);
        Route::delete('/loginhistorys/{LoginId}',[LoginhistoryController::class, 'destroy']);

        // Food & Drinks
        Route::post('/foodanddrinks',           [FoodanddrinkController::class, 'store']);
        Route::put('/foodanddrinks/{ItemId}',   [FoodanddrinkController::class, 'update']);
        Route::delete('/foodanddrinks/{ItemId}',[FoodanddrinkController::class, 'destroy']);

        // Roles
        Route::post('/roles',           [RoleController::class, 'store']);
        Route::put('/roles/{RoleId}',   [RoleController::class, 'update']);
        Route::delete('/roles/{RoleId}',[RoleController::class, 'destroy']);

        // Users
        Route::post('/users',            [UserController::class, 'store']);
        Route::put('/users/{UserId}',    [UserController::class, 'update']);
        Route::delete('/users/{UserId}', [UserController::class, 'destroy']);

        // Notifications
        Route::post('/notifications',                   [NotificationController::class, 'store']);
        Route::put('/notifications/{NotificationId}',   [NotificationController::class, 'update']);
        Route::delete('/notifications/{NotificationId}',[NotificationController::class, 'destroy']);

        // Orders
        Route::post('/orders',            [OrderController::class, 'store']);
        Route::put('/orders/{OrderId}',   [OrderController::class, 'update']);
        Route::delete('/orders/{OrderId}',[OrderController::class, 'destroy']);

        // News
        Route::post('/news',           [NewsController::class, 'store']);
        Route::put('/news/{NewsId}',   [NewsController::class, 'update']);
        Route::delete('/news/{NewsId}',[NewsController::class, 'destroy']);

        // Rooms
        Route::post('/rooms',           [RoomController::class, 'store']);
        Route::put('/rooms/{RoomId}',   [RoomController::class, 'update']);
        Route::delete('/rooms/{RoomId}',[RoomController::class, 'destroy']);

        // Memberships
        Route::post('/memberships',                [MembershipController::class, 'store']);
        Route::put('/memberships/{MembershipId}',  [MembershipController::class, 'update']);
        Route::delete('/memberships/{MembershipId}',[MembershipController::class, 'destroy']);

        // Movies — getPaged đã public ở trên, chỉ cần store/update/destroy
        Route::post('/movies',            [MovieController::class, 'store']);
        Route::put('/movies/{MovieId}',   [MovieController::class, 'update']);
        Route::delete('/movies/{MovieId}',[MovieController::class, 'destroy']);

        // Movie Casts
        Route::post('/moviecasts',           [MoviecastController::class, 'store']);
        Route::put('/moviecasts/{CastId}',   [MoviecastController::class, 'update']);
        Route::delete('/moviecasts/{CastId}',[MoviecastController::class, 'destroy']);

        // Movie Genres
        Route::post('/moviegenres',                 [MoviegenreController::class, 'store']);
        Route::put('/moviegenres/{MovieGenreId}',   [MoviegenreController::class, 'update']);
        Route::delete('/moviegenres/{MovieGenreId}',[MoviegenreController::class, 'destroy']);

        // Payments
        Route::post('/payments',             [PaymentController::class, 'store']);
        Route::put('/payments/{PaymentId}',  [PaymentController::class, 'update']);
        Route::delete('/payments/{PaymentId}',[PaymentController::class, 'destroy']);

        // Seats
        Route::post('/seats/bulk',           [SeatController::class, 'bulkStore']);
        Route::post('/seats',                [SeatController::class, 'store']);
        Route::put('/seats/{SeatId}',        [SeatController::class, 'update']);
        Route::delete('/seats/{SeatId}',     [SeatController::class, 'destroy']);
        Route::delete('/seats/room/{roomId}',[SeatController::class, 'deleteByRoom']);

        // Showtime Seats
        Route::post('/showtimeseats',        [ShowtimeseatController::class, 'store']);
        Route::put('/showtimeseats/{Id}',    [ShowtimeseatController::class, 'update']);
        Route::delete('/showtimeseats/{Id}', [ShowtimeseatController::class, 'destroy']);

        // Order Details
        Route::post('/orderdetails',                   [OrderdetailController::class, 'store']);
        Route::put('/orderdetails/{OrderDetailId}',    [OrderdetailController::class, 'update']);
        Route::delete('/orderdetails/{OrderDetailId}', [OrderdetailController::class, 'destroy']);

        // Promotions
        Route::post('/promotions',               [PromotionController::class, 'store']);
        Route::put('/promotions/{PromotionId}',  [PromotionController::class, 'update']);
        Route::delete('/promotions/{PromotionId}',[PromotionController::class, 'destroy']);

        // Tickets
        Route::post('/tickets',            [TicketController::class, 'store']);
        Route::put('/tickets/{TicketId}',  [TicketController::class, 'update']);
        Route::delete('/tickets/{TicketId}',[TicketController::class, 'destroy']);

        // Schedules
        Route::post('/schedules',               [ScheduleController::class, 'store']);
        Route::put('/schedules/{ScheduleId}',   [ScheduleController::class, 'update']);
        Route::delete('/schedules/{ScheduleId}',[ScheduleController::class, 'destroy']);

        // Wishlists
        Route::post('/wishlists',               [WishlistController::class, 'store']);
        Route::put('/wishlists/{WishlistId}',   [WishlistController::class, 'update']);
        Route::delete('/wishlists/{WishlistId}',[WishlistController::class, 'destroy']);

        // Reviews
        Route::post('/reviews',            [ReviewController::class, 'store']);
        Route::put('/reviews/{ReviewId}',  [ReviewController::class, 'update']);
        Route::delete('/reviews/{ReviewId}',[ReviewController::class, 'destroy']);

        // Showtimes
        Route::post('/showtimes',               [ShowtimeController::class, 'store']);
        Route::put('/showtimes/{ShowtimeId}',   [ShowtimeController::class, 'update']);
        Route::delete('/showtimes/{ShowtimeId}',[ShowtimeController::class, 'destroy']);

        // Staffs
        Route::post('/staffs',           [StaffController::class, 'store']);
        Route::put('/staffs/{StaffId}',  [StaffController::class, 'update']);
        Route::delete('/staffs/{StaffId}',[StaffController::class, 'destroy']);
    });
});