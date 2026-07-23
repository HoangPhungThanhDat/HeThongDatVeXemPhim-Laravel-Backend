<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userRole = $user->role ? $user->role->RoleName : null;

        // ✅ So sánh không phân biệt hoa thường
        $rolesLower    = array_map('strtolower', $roles);
        $userRoleLower = strtolower($userRole ?? '');

        if (!$userRole || !in_array($userRoleLower, $rolesLower)) {
            return response()->json([
                'message'       => 'Forbidden - Role required: ' . implode(' or ', $roles),
                'your_role'     => $userRole,       // 👈 debug: thấy role thực tế trong DB
                'required_roles'=> $roles,
            ], 403);
        }

        return $next($request);
    }
}