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
use App\Http\Controllers\Api\SeatManagementController;
use App\Http\Controllers\Api\RoomSeatController;
use App\Http\Controllers\Api\RoomManagementController;

// ========================================================
// 🔓 PUBLIC ROUTES (Không cần token)
// ========================================================

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login',    [AuthController::class, 'login']);

// ⭐ REFRESH TOKEN - Public, dùng HttpOnly cookie
Route::prefix('auth')->group(function () {
    Route::post('/refresh', [AuthController::class, 'refresh'])
        ->middleware('throttle:20,1');
});

// ========================================================
// 🔓 GOOGLE OAUTH ROUTES
// ========================================================
Route::prefix('auth')->group(function () {
    Route::get('/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
    Route::get('/google/check', [AuthController::class, 'checkGoogleLogin'])->name('auth.google.check');
});

// ========================================================
// 📽️ MOVIES
// ========================================================
Route::prefix('movies')->group(function () {
     // ✅ AUTO UPDATE STATUS - Thêm 2 routes mới
     Route::post('/auto-update-status', [MovieController::class, 'autoUpdateStatus'])
     ->middleware(['auth:api', 'checkrole:Admin']);
 
    Route::post('/update-status/{movieId}', [MovieController::class, 'updateSingleStatus'])
     ->middleware(['auth:api', 'checkrole:Admin']);

    Route::get('/now-showing/all',              [MovieController::class, 'getNowShowing']);
    Route::get('/now-showing/filters',          [MovieController::class, 'getNowShowingFilters']);
    Route::post('/now-showing/filter',          [MovieController::class, 'filterNowShowing']);
    Route::get('/now-showing/search',           [MovieController::class, 'searchNowShowing']);
    Route::get('/now-showing/genre/{genreId}',  [MovieController::class, 'getNowShowingByGenre']);
    Route::get('/coming-soon',                  [MovieController::class, 'getComingSoon']);
    Route::get('/hot',                          [MovieController::class, 'getHotMovies']);
    Route::get('/latest',                       [MovieController::class, 'getLatestMovies']);
    Route::get('/statistics/overview',          [MovieController::class, 'getStatistics']);
    Route::get('/by-status/{status}',           [MovieController::class, 'getByStatus']);
    Route::get('/{id}/detail',                  [MovieController::class, 'getDetail']);
    Route::get('/',                             [MovieController::class, 'index']);
    Route::get('/paged',                        [MovieController::class, 'getPaged']);
    Route::get('/{MovieId}',                    [MovieController::class, 'show']);
});

// ========================================================
// 🎬 MOVIE CASTS
// ========================================================
Route::get('/moviecasts/paged', [MoviecastController::class, 'getPaged']);
Route::get('/moviecasts',       [MoviecastController::class, 'index']);
Route::get('/moviecasts/{CastId}', [MoviecastController::class, 'show']);

// ========================================================
// 🏷️ GENRES
// ========================================================
Route::get('/genres',           [GenreController::class, 'index']);
Route::get('/genres/{GenreId}', [GenreController::class, 'show']);

// ========================================================
// 📋 AUDITLOGS
// ========================================================
Route::get('/auditlogs',          [AuditlogController::class, 'index']);
Route::get('/auditlogs/{LogId}',  [AuditlogController::class, 'show']);

// ========================================================
// 🖼️ BANNERS
// ========================================================
Route::get('/banners',            [BannerController::class, 'index']);
Route::get('/banners/{BannerId}', [BannerController::class, 'show']);

// ========================================================
// 🏢 CINEMAS
// ========================================================
Route::prefix('cinemas')->group(function () {
    Route::get('/',                      [CinemaController::class, 'index']);
    Route::get('/{CinemaId}',            [CinemaController::class, 'show']);
    Route::get('/{cinemaId}/showtimes',  [CinemaController::class, 'showtimes']);


    Route::get('/{cinemaId}/movies', [CinemaController::class, 'getMoviesByCinema']);
    Route::get('/{cinemaId}/movies/{movieId}/dates', [CinemaController::class, 'getDatesByCinemaAndMovie']);
    Route::get('/{cinemaId}/movies/{movieId}/showtimes', [CinemaController::class, 'getShowtimesByMovieAndDate']);
});

Route::prefix('movies')->group(function () {
    Route::get('/{movieId}/cinemas',    [CinemaController::class, 'cinemasByMovie']);
    Route::get('/{movieId}/showtimes',  [CinemaController::class, 'showtimesByMovie']);
});

// ========================================================
// 📞 CONTACTS
// ========================================================
Route::get('/contacts',             [ContactController::class, 'index']);
Route::get('/contacts/{ContactId}', [ContactController::class, 'show']);
Route::post('/contacts',            [ContactController::class, 'store']);

// ========================================================
// 🏢 DISTRIBUTORS
// ========================================================
Route::get('/distributors',                 [DistributorController::class, 'index']);
Route::get('/distributors/{distributorId}', [DistributorController::class, 'show']);

// ========================================================
// 📜 LOGIN HISTORY
// ========================================================
Route::get('/loginhistorys',           [LoginhistoryController::class, 'index']);
Route::get('/loginhistorys/{LoginId}', [LoginhistoryController::class, 'show']);

// ========================================================
// 🍿 FOOD & DRINKS
// ========================================================
Route::get('/foodanddrinks',          [FoodanddrinkController::class, 'index']);
Route::get('/foodanddrinks/{ItemId}', [FoodanddrinkController::class, 'show']);

// ========================================================
// 👥 ROLES
// ========================================================
Route::get('/roles',          [RoleController::class, 'index']);
Route::get('/roles/{RoleId}', [RoleController::class, 'show']);

// ========================================================
// 👤 USERS
// ========================================================
Route::get('/users',          [UserController::class, 'index']);
Route::get('/users/paged',    [UserController::class, 'getPaged']);
Route::get('/users/{UserId}', [UserController::class, 'show']);

// ========================================================
// 🔔 NOTIFICATIONS
// ========================================================
Route::get('/notifications',                  [NotificationController::class, 'index']);
Route::get('/notifications/{NotificationId}', [NotificationController::class, 'show']);

// ========================================================
// 📦 ORDERS - PUBLIC
// ========================================================
Route::prefix('orders')->group(function () {
    Route::get('/',                           [OrderController::class, 'index']);
    Route::get('/{OrderId}',                  [OrderController::class, 'show']);
    Route::get('/{OrderId}/details',          [OrderController::class, 'getOrderDetails']);
    Route::get('/user/{UserId}/all',          [OrderController::class, 'getUserOrders']);
});

// ========================================================
// 📰 NEWS
// ========================================================
Route::get('/news',          [NewsController::class, 'index']);
Route::get('/news/{NewsId}', [NewsController::class, 'show']);

// ========================================================
// 🚪 ROOMS
// ========================================================
Route::get('/rooms',          [RoomController::class, 'index']);
Route::get('/rooms/{RoomId}', [RoomController::class, 'show']);

// ========================================================
// 👑 MEMBERSHIPS
// ========================================================
Route::get('/memberships',                [MembershipController::class, 'index']);
Route::get('/memberships/{MembershipId}', [MembershipController::class, 'show']);

// ========================================================
// 🎭 MOVIE GENRES
// ========================================================
Route::get('/moviegenres',                [MoviegenreController::class, 'index']);
Route::get('/moviegenres/{MovieGenreId}', [MoviegenreController::class, 'show']);

// ========================================================
// 💳 PAYMENTS
// ========================================================
Route::get('/payments',             [PaymentController::class, 'index']);
Route::get('/payments/{PaymentId}', [PaymentController::class, 'show']);

// ========================================================
// 💺 SEATS
// ========================================================
Route::get('/seats',          [SeatController::class, 'index']);
Route::get('/seats/{SeatId}', [SeatController::class, 'show']);

// ========================================================
// 🎫 SHOWTIME SEATS (Cũ - Giữ để tương thích)
// ========================================================
Route::prefix('showtimeseats')->group(function () {
    Route::get('/',          [ShowtimeseatController::class, 'index']);
    Route::get('/paged',     [ShowtimeseatController::class, 'getPaged']);
    Route::get('/{Id}',      [ShowtimeseatController::class, 'show']);
});

// ========================================================
// 📋 ORDER DETAILS
// ========================================================
Route::get('/orderdetails',                 [OrderdetailController::class, 'index']);
Route::get('/orderdetails/{OrderDetailId}', [OrderdetailController::class, 'show']);

// ========================================================
// 🎁 PROMOTIONS
// ========================================================
Route::get('/promotions',               [PromotionController::class, 'index']);
Route::get('/promotions/paged',         [PromotionController::class, 'getPaged']);
Route::get('/promotions/{PromotionId}', [PromotionController::class, 'show']);

// ========================================================
// 🎟️ TICKETS
// ========================================================
Route::get('/tickets',            [TicketController::class, 'index']);
Route::get('/tickets/{TicketId}', [TicketController::class, 'show']);

// ========================================================
// 📅 SCHEDULES
// ========================================================
Route::get('/schedules',              [ScheduleController::class, 'index']);
Route::get('/schedules/{ScheduleId}', [ScheduleController::class, 'show']);

// ========================================================
// ❤️ WISHLISTS
// ========================================================
Route::get('/wishlists',              [WishlistController::class, 'index']);
Route::get('/wishlists/{WishlistId}', [WishlistController::class, 'show']);

// ========================================================
// ⭐ REVIEWS
// ========================================================
Route::get('/reviews',            [ReviewController::class, 'index']);
Route::get('/reviews/{ReviewId}', [ReviewController::class, 'show']);

// ========================================================
// 🕐 SHOWTIMES (Cũ - Giữ để tương thích)
// ========================================================
Route::prefix('showtimes')->group(function () {
    Route::get('/',                              [ShowtimeController::class, 'index']);
    Route::get('/paged',                         [ShowtimeController::class, 'getPaged']);
    Route::get('/{ShowtimeId}',                  [ShowtimeController::class, 'show']);
    Route::get('/{showtimeId}/seats',            [ShowtimeseatController::class, 'getSeatsByShowtime']);
    Route::post('/{showtimeId}/generate-seats',  [ShowtimeseatController::class, 'generateSeats']);
});

// ========================================================
// 👔 STAFFS
// ========================================================
Route::get('/staffs',           [StaffController::class, 'index']);
Route::get('/staffs/{StaffId}', [StaffController::class, 'show']);

// ========================================================
// 🪑 QUẢN LÝ TRẠNG THÁI GHẾ (THEO SUẤT CHIẾU)
// ========================================================
Route::prefix('seat-management')->group(function () {
    Route::get('/data', [SeatManagementController::class, 'getData']);
    Route::get('/showtime/{showtimeId}/seats', [SeatManagementController::class, 'getSeatsByShowtime']);
    Route::post('/generate', [SeatManagementController::class, 'generateSeats']);
    Route::put('/seat', [SeatManagementController::class, 'updateSeat']);
    Route::post('/reset', [SeatManagementController::class, 'resetSeat']);
    Route::delete('/seat', [SeatManagementController::class, 'deleteSeat']);
});

// ========================================================
// 🪑 QUẢN LÝ GHẾ PHÒNG CHIẾU (ROOM SEAT - MỚI)
// ========================================================
Route::prefix('room-seat')->group(function () {
    Route::get('/cinemas', [RoomSeatController::class, 'getCinemas']);
    Route::get('/rooms', [RoomSeatController::class, 'getRooms']);
    Route::get('/room/{roomId}/seats', [RoomSeatController::class, 'getSeats']);
    Route::post('/room/{roomId}/add-rows', [RoomSeatController::class, 'addRows']);
    Route::post('/room/{roomId}/add-cols', [RoomSeatController::class, 'addCols']);
    Route::put('/seat/{seatId}', [RoomSeatController::class, 'updateSeat']);
    Route::delete('/room/{roomId}/row/{row}', [RoomSeatController::class, 'deleteRow']);
    Route::delete('/room/{roomId}/col/{col}', [RoomSeatController::class, 'deleteCol']);
    Route::delete('/seat/{seatId}', [RoomSeatController::class, 'deleteSeat']);
    Route::post('/room/{roomId}/reset', [RoomSeatController::class, 'resetSeats']);
});

// ========================================================
// 📊 QUẢN LÝ PHÒNG CHIẾU (TỔNG HỢP - MỚI)
// ========================================================
Route::prefix('room-management')->middleware(['auth:api', 'checkrole:Admin'])->group(function () {
    Route::get('/dashboard', [RoomManagementController::class, 'getDashboardData']);
    Route::get('/statistics', [RoomManagementController::class, 'getStatistics']);
    Route::get('/rooms/{roomId}/detail', [RoomManagementController::class, 'getRoomDetail']);
    Route::put('/rooms/{roomId}/seats', [RoomManagementController::class, 'updateSeats']);
});

// ========================================================
// 🔐 PROTECTED ROUTES (Cần JWT token)
// ========================================================

Route::middleware(['auth:api'])->group(function () {

    // ========================================================
    // 🔑 AUTH
    // ========================================================
    Route::post('/logout', [AuthController::class, 'logout']);

    // ⭐ LOGOUT ALL DEVICES + ME
    Route::prefix('auth')->group(function () {
        Route::post('/logout-all', [AuthController::class, 'logoutAll']);
        Route::get('/me',          [AuthController::class, 'me']);
    });

    // ========================================================
    // 👤 PROFILE
    // ========================================================
    Route::get('/profile',                [UserController::class, 'getProfile']);
    Route::put('/profile',                [UserController::class, 'updateProfile']);
    Route::put('/users/{UserId}/profile', [UserController::class, 'updateProfile']);
    Route::put('/change-password',        [UserController::class, 'changePassword']);

    // ========================================================
    // 📦 MY ORDERS (Người dùng xem đơn hàng của mình)
    // ========================================================
    Route::prefix('my-orders')->group(function () {
        Route::get('/',                         [OrderController::class, 'getMyOrders']);
        Route::get('/{OrderId}/details',        [OrderController::class, 'getMyOrderDetails']);
    });

    // ========================================================
    // 👨‍💼 ADMIN & STAFF
    // ========================================================
    Route::middleware(['checkrole:Admin,Staff'])->group(function () {

        // Banners
        Route::post('/banners',              [BannerController::class, 'store']);
        Route::put('/banners/{BannerId}',    [BannerController::class, 'update']);
        Route::delete('/banners/{BannerId}', [BannerController::class, 'destroy']);

        // Promotions
        Route::post('/promotions',                [PromotionController::class, 'store']);
        Route::put('/promotions/{PromotionId}',   [PromotionController::class, 'update']);
        Route::delete('/promotions/{PromotionId}',[PromotionController::class, 'destroy']);

        // News
        Route::post('/news',            [NewsController::class, 'store']);
        Route::put('/news/{NewsId}',    [NewsController::class, 'update']);
        Route::delete('/news/{NewsId}', [NewsController::class, 'destroy']);
    });

    // ========================================================
    // 👑 ADMIN ONLY
    // ========================================================
    Route::middleware(['checkrole:Admin'])->group(function () {

        // Auditlogs
        Route::post('/auditlogs',           [AuditlogController::class, 'store']);
        Route::put('/auditlogs/{LogId}',    [AuditlogController::class, 'update']);
        Route::delete('/auditlogs/{LogId}', [AuditlogController::class, 'destroy']);

        // Genres
        Route::post('/genres',             [GenreController::class, 'store']);
        Route::put('/genres/{GenreId}',    [GenreController::class, 'update']);
        Route::delete('/genres/{GenreId}', [GenreController::class, 'destroy']);

        // Cinemas
        Route::post('/cinemas',              [CinemaController::class, 'store']);
        Route::put('/cinemas/{CinemaId}',    [CinemaController::class, 'update']);
        Route::delete('/cinemas/{CinemaId}', [CinemaController::class, 'destroy']);

        // Contacts
        Route::put('/contacts/{ContactId}',    [ContactController::class, 'update']);
        Route::delete('/contacts/{ContactId}', [ContactController::class, 'destroy']);

        // Distributors
        Route::post('/distributors',               [DistributorController::class, 'store']);
        Route::put('/distributors/{distributorId}',   [DistributorController::class, 'update']);
        Route::delete('/distributors/{distributorId}',[DistributorController::class, 'destroy']);

        // Login History
        Route::post('/loginhistorys',             [LoginhistoryController::class, 'store']);
        Route::put('/loginhistorys/{LoginId}',    [LoginhistoryController::class, 'update']);
        Route::delete('/loginhistorys/{LoginId}', [LoginhistoryController::class, 'destroy']);

        // Food & Drinks
        Route::post('/foodanddrinks',            [FoodanddrinkController::class, 'store']);
        Route::put('/foodanddrinks/{ItemId}',    [FoodanddrinkController::class, 'update']);
        Route::delete('/foodanddrinks/{ItemId}', [FoodanddrinkController::class, 'destroy']);

        // Roles
        Route::post('/roles',            [RoleController::class, 'store']);
        Route::put('/roles/{RoleId}',    [RoleController::class, 'update']);
        Route::delete('/roles/{RoleId}', [RoleController::class, 'destroy']);

        // Users
        Route::post('/users',             [UserController::class, 'store']);
        Route::put('/users/{UserId}',     [UserController::class, 'update']);
        Route::delete('/users/{UserId}',  [UserController::class, 'destroy']);

        // Notifications
        Route::post('/notifications',                    [NotificationController::class, 'store']);
        Route::put('/notifications/{NotificationId}',    [NotificationController::class, 'update']);
        Route::delete('/notifications/{NotificationId}', [NotificationController::class, 'destroy']);

        // Orders - ADMIN
        Route::prefix('orders')->group(function () {
            Route::post('/',             [OrderController::class, 'store']);
            Route::put('/{OrderId}',     [OrderController::class, 'update']);
            Route::delete('/{OrderId}',  [OrderController::class, 'destroy']);
        });

        // Rooms
        Route::post('/rooms',            [RoomController::class, 'store']);
        Route::put('/rooms/{RoomId}',    [RoomController::class, 'update']);
        Route::delete('/rooms/{RoomId}', [RoomController::class, 'destroy']);

        // Memberships
        Route::post('/memberships',                 [MembershipController::class, 'store']);
        Route::put('/memberships/{MembershipId}',   [MembershipController::class, 'update']);
        Route::delete('/memberships/{MembershipId}',[MembershipController::class, 'destroy']);

        // Movies
        Route::post('/movies',             [MovieController::class, 'store']);
        Route::put('/movies/{MovieId}',    [MovieController::class, 'update']);
        Route::delete('/movies/{MovieId}', [MovieController::class, 'destroy']);

        // Movie Casts
        Route::post('/moviecasts',            [MoviecastController::class, 'store']);
        Route::put('/moviecasts/{CastId}',    [MoviecastController::class, 'update']);
        Route::delete('/moviecasts/{CastId}', [MoviecastController::class, 'destroy']);

        // Movie Genres
        Route::post('/moviegenres',                  [MoviegenreController::class, 'store']);
        Route::put('/moviegenres/{MovieGenreId}',    [MoviegenreController::class, 'update']);
        Route::delete('/moviegenres/{MovieGenreId}', [MoviegenreController::class, 'destroy']);

        // Payments
        Route::post('/payments',              [PaymentController::class, 'store']);
        Route::put('/payments/{PaymentId}',   [PaymentController::class, 'update']);
        Route::delete('/payments/{PaymentId}',[PaymentController::class, 'destroy']);

        // Seats (Cũ - Giữ để tương thích)
        Route::post('/seats/bulk',            [SeatController::class, 'bulkStore']);
        Route::post('/seats',                 [SeatController::class, 'store']);
        Route::put('/seats/{SeatId}',         [SeatController::class, 'update']);
        Route::delete('/seats/{SeatId}',      [SeatController::class, 'destroy']);
        Route::delete('/seats/room/{roomId}', [SeatController::class, 'deleteByRoom']);

        // Showtime Seats - ADMIN
        Route::post('/showtimeseats',         [ShowtimeseatController::class, 'store']);
        Route::put('/showtimeseats/{Id}',     [ShowtimeseatController::class, 'update']);
        Route::delete('/showtimeseats/{Id}',  [ShowtimeseatController::class, 'destroy']);

        // Order Details
        Route::post('/orderdetails',                    [OrderdetailController::class, 'store']);
        Route::put('/orderdetails/{OrderDetailId}',     [OrderdetailController::class, 'update']);
        Route::delete('/orderdetails/{OrderDetailId}',  [OrderdetailController::class, 'destroy']);

        // Tickets
        Route::post('/tickets',             [TicketController::class, 'store']);
        Route::put('/tickets/{TicketId}',   [TicketController::class, 'update']);
        Route::delete('/tickets/{TicketId}',[TicketController::class, 'destroy']);

        // Schedules
        Route::post('/schedules',                [ScheduleController::class, 'store']);
        Route::put('/schedules/{ScheduleId}',    [ScheduleController::class, 'update']);
        Route::delete('/schedules/{ScheduleId}', [ScheduleController::class, 'destroy']);

        // Wishlists
        Route::post('/wishlists',                [WishlistController::class, 'store']);
        Route::put('/wishlists/{WishlistId}',    [WishlistController::class, 'update']);
        Route::delete('/wishlists/{WishlistId}', [WishlistController::class, 'destroy']);

        // Reviews
        Route::post('/reviews',             [ReviewController::class, 'store']);
        Route::put('/reviews/{ReviewId}',   [ReviewController::class, 'update']);
        Route::delete('/reviews/{ReviewId}',[ReviewController::class, 'destroy']);

        // Showtimes
        Route::post('/showtimes',                [ShowtimeController::class, 'store']);
        Route::put('/showtimes/{ShowtimeId}',    [ShowtimeController::class, 'update']);
        Route::delete('/showtimes/{ShowtimeId}', [ShowtimeController::class, 'destroy']);

        // Staffs
        Route::post('/staffs',            [StaffController::class, 'store']);
        Route::put('/staffs/{StaffId}',   [StaffController::class, 'update']);
        Route::delete('/staffs/{StaffId}',[StaffController::class, 'destroy']);
    });
});