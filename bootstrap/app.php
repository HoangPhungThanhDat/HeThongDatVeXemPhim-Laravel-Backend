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
        // Thêm CORS middleware cho API routes
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);

        // Alias middleware tùy chỉnh
        $middleware->alias([
            'checkrole' => \App\Http\Middleware\CheckRoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // ════════════════════════════════════════════════════════
        // ⭐ JWT EXCEPTIONS — Trả JSON 401 thay vì redirect
        // ════════════════════════════════════════════════════════

        // Token hết hạn
        $exceptions->render(function (
            \Tymon\JWTAuth\Exceptions\TokenExpiredException $e,
            Request $request
        ) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token đã hết hạn',
                    'code'    => 'TOKEN_EXPIRED',
                ], 401);
            }
        });

        // Token không hợp lệ
        $exceptions->render(function (
            \Tymon\JWTAuth\Exceptions\TokenInvalidException $e,
            Request $request
        ) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token không hợp lệ',
                    'code'    => 'TOKEN_INVALID',
                ], 401);
            }
        });

        // Không có token
        $exceptions->render(function (
            \Tymon\JWTAuth\Exceptions\JWTException $e,
            Request $request
        ) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy token',
                    'code'    => 'TOKEN_ABSENT',
                ], 401);
            }
        });

        // Laravel Authentication Exception
        $exceptions->render(function (
            \Illuminate\Auth\AuthenticationException $e,
            Request $request
        ) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Chưa xác thực',
                    'code'    => 'UNAUTHENTICATED',
                ], 401);
            }
        });
    })->create();