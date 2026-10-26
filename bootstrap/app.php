<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function() {
            Route::middleware('api')
                ->prefix('api')
                ->name('api.')
                ->group(base_path('routes/api.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 1. Thêm CORS middleware cho API routes
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // 2. ⭐ ĐĂNG KÝ MIDDLEWARE GHI LOG VÀO SAU CÙNG CỦA NHÓM API
        // Để đảm bảo nó chạy SAU KHI xác thực JWT (auth:api) hoàn tất
        $middleware->api(append: [
            \App\Http\Middleware\AuditLogMiddleware::class,
        ]);

        // 3. Alias middleware tùy chỉnh
        $middleware->alias([
            'checkrole' => \App\Http\Middleware\CheckRoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // ... (Giữ nguyên phần xử lý lỗi JWT như file cũ của bạn)
        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Token đã hết hạn', 'code' => 'TOKEN_EXPIRED'], 401);
            }
        });
        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Token không hợp lệ', 'code' => 'TOKEN_INVALID'], 401);
            }
        });
        $exceptions->render(function (\Tymon\JWTAuth\Exceptions\JWTException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Không tìm thấy token', 'code' => 'TOKEN_ABSENT'], 401);
            }
        });
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['success' => false, 'message' => 'Chưa xác thực', 'code' => 'UNAUTHENTICATED'], 401);
            }
        });
    })->create();