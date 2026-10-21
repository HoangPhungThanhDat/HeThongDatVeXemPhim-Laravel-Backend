<?php

namespace App\Repositories;

use App\Models\Moviecast;
use Illuminate\Support\Facades\Cache;

class MoviecastRepository
{
    const CACHE_TIME = 3600; // Cache 1 tiếng

    public function all()
    {
        return Cache::remember('moviecasts_all_v2', self::CACHE_TIME, function () {
            return Moviecast::with('movie')->orderBy('Name', 'asc')->get();
        });
    }

    /**
     * ✅ Lấy dữ liệu phân trang (Đã thêm lọc STATUS)
     */
    public function getPaged($page, $limit, $search = '', $role = '', $status = '')
    {
        // ✅ Đưa $status vào cache key để tránh lỗi lấy dữ liệu cũ
        $cacheKey = "moviecasts_paged_v2_p{$page}_l{$limit}_" . md5($search . $role . $status);

        return Cache::remember($cacheKey, self::CACHE_TIME, function () use ($page, $limit, $search, $role, $status) {
            $query = Moviecast::with('movie');

            // 1. Lọc theo tên
            if ($search) {
                $query->where('Name', 'like', "%{$search}%");
            }

            // 2. Lọc theo vai trò
            if ($role) {
                $query->where('Role', $role);
            }

            // 3. ✅ Lọc theo trạng thái (Active/Inactive)
            if ($status) {
                $query->where('Status', $status);
            }

            $total      = $query->count();
            $totalPages = (int) ceil($total / $limit);
            
            $items      = $query->orderBy('CastId', 'desc')
                                ->skip(($page - 1) * $limit)
                                ->take($limit)
                                ->get();

            return [
                'data'       => $items,
                'total'      => $total,
                'page'       => $page,
                'limit'      => $limit,
                'totalPages' => $totalPages,
            ];
        });
    }

    public function find($CastId)
    {
        return Cache::remember("moviecast_detail_{$CastId}", self::CACHE_TIME, function () use ($CastId) {
            return Moviecast::find($CastId);
        });
    }

    public function create(array $data)
    {
        $moviecast = Moviecast::create($data);
        $this->clearCache();
        return $moviecast;
    }

    public function update(Moviecast $moviecast, array $data)
    {
        $moviecast->update($data);
        $this->clearCache($moviecast->CastId);
        return $moviecast;
    }

    public function delete(Moviecast $moviecast)
    {
        $id = $moviecast->CastId;
        $result = $moviecast->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Xóa Cache liên quan
     */
    public function clearCache($id = null)
    {
        Cache::forget('moviecasts_all_v2');
        
        // Xóa cache phân trang v2
        for ($page = 1; $page <= 10; $page++) {
            for ($limit = 10; $limit <= 100; $limit += 10) {
                Cache::forget("moviecasts_paged_v2_p{$page}_l{$limit}_" . md5('' . ''));
                Cache::forget("moviecasts_paged_v2_p{$page}_l{$limit}_" . md5('' . 'Actor'));
                Cache::forget("moviecasts_paged_v2_p{$page}_l{$limit}_" . md5('' . 'Director'));
                Cache::forget("moviecasts_paged_v2_p{$page}_l{$limit}_" . md5('' . 'Active'));
                Cache::forget("moviecasts_paged_v2_p{$page}_l{$limit}_" . md5('' . 'Inactive'));
            }
        }

        if ($id) {
            Cache::forget("moviecast_detail_{$id}");
        }
    }
}