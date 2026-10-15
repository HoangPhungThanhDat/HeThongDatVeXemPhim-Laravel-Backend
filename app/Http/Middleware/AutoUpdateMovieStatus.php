<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Movie;
use Illuminate\Support\Facades\Log; 
use Symfony\Component\HttpFoundation\Response;

class AutoUpdateMovieStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ✅ Cập nhật trạng thái cho tất cả phim
        // Chỉ chạy khi request API và không phải AJAX để tránh ảnh hưởng performance
        if ($request->wantsJson() && !$request->ajax()) {
            try {
                Movie::autoUpdateAllStatus();
            } catch (\Exception $e) {
                // Log lỗi nhưng không ảnh hưởng request
                Log::error('AutoUpdateMovieStatus middleware error: ' . $e->getMessage());
            }
        }
        
        return $next($request);
    }
}