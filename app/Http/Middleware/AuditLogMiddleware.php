<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Auditlog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth; // ⭐ Thêm thư viện JWT

class AuditLogMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // ⭐ Lấy user từ token JWT (vì Middleware chạy trước auth:api)
        $user = null;
        try {
            if ($request->bearerToken()) {
                $user = JWTAuth::setToken($request->bearerToken())->authenticate();
            }
        } catch (\Exception $e) {
            // Bỏ qua nếu token không hợp lệ
        }

        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            
            if (!str_contains($request->path(), 'auth/refresh') && !str_contains($request->path(), 'auth/logout')) {
                
                $action = $this->getActionType($request->method());
                $description = $this->getDescription($request, $action);

                try {
                    Auditlog::create([
                        'UserId' => $user ? $user->UserId : null, // ⭐ Bây giờ đã lấy được UserId
                        'Action' => $action,
                        'Description' => $description,
                        'IpAddress' => $request->ip() === '::1' ? '127.0.0.1' : $request->ip(), // ⭐ Sửa IP
                        'DeviceInfo' => $request->userAgent(),
                        'CreatedAt' => Carbon::now(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Lỗi ghi AuditLog: ' . $e->getMessage());
                }
            }
        }

        return $next($request);
    }

    private function getActionType($method)
    {
        switch ($method) {
            case 'POST': return 'Create';
            case 'PUT':
            case 'PATCH': return 'Update';
            case 'DELETE': return 'Delete';
            default: return 'Unknown';
        }
    }

    private function getDescription($request, $action)
    {
        $path = $request->path();
        // Lấy tên user an toàn
        $userEmail = $request->user() ? $request->user()->Email : 'Khách';
        
        if (str_contains($path, 'movies')) return "{$userEmail} đã thao tác trên module Phim";
        if (str_contains($path, 'tickets')) return "{$userEmail} đã thao tác trên module Vé";
        if (str_contains($path, 'rooms')) return "{$userEmail} đã thao tác trên module Phòng chiếu";
        if (str_contains($path, 'showtimes')) return "{$userEmail} đã thao tác trên module Suất chiếu";
        if (str_contains($path, 'promotions')) return "{$userEmail} đã thao tác trên module Khuyến mãi";
        if (str_contains($path, 'banners')) return "{$userEmail} đã thao tác trên module Banner";
        if (str_contains($path, 'foodanddrinks')) return "{$userEmail} đã thao tác trên module Combo/Bắp nước";
        
        return "{$userEmail} đã gọi API: [{$request->method()}] /{$path}";
    }
}