<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = $request->user();

        // Nếu chưa đăng nhập
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Lấy role từ quan hệ
        $userRole = $user->role ? $user->role->RoleName : null;

        if ($userRole !== $role) {
            return response()->json(['message' => 'Forbidden - Role required: '.$role], 403);
        }

        return $next($request);
    }
}