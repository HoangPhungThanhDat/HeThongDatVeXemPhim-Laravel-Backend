<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Cache;

class UserRepository
{
    const CACHE_TIME = 600; // Cache 10 phút

    /**
     * Lấy tất cả User
     */
    public function all()
    {
        return Cache::remember('users_all', self::CACHE_TIME, function () {
            return User::with('role')->orderBy('UserId', 'desc')->get();
        });
    }

    /**
     * Lấy dữ liệu phân trang
     */
    public function getPaged($page, $limit, $search = '', $status = '')
    {
        $version  = Cache::get('users_paged_version', 1);
        $cacheKey = "users_paged_v{$version}_p{$page}_l{$limit}_" . md5($search . '|' . $status);

        return Cache::remember($cacheKey, self::CACHE_TIME, function () use ($page, $limit, $search, $status) {
            $query = User::with('role');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('FullName', 'like', "%{$search}%")
                        ->orWhere('Email', 'like', "%{$search}%")
                        ->orWhere('PhoneNumber', 'like', "%{$search}%");
                });
            }

            if ($status) {
                $query->where('Status', $status);
            }

            $total      = $query->count();
            $totalPages = (int) ceil($total / $limit);
            $items      = $query->orderBy('UserId', 'desc')
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

    /**
     * Tìm User theo ID
     */
    public function find($UserId)
    {
        return Cache::remember("user_detail_{$UserId}", self::CACHE_TIME, function () use ($UserId) {
            return User::with('role')->find($UserId);
        });
    }

    /**
     * ✅ Kiểm tra Email tồn tại (loại trừ user hiện tại)
     */
    public function checkEmailExists($email, $excludeUserId = null)
    {
        $query = User::where('Email', $email);

        // ✅ FIX: dùng !== null thay vì if(...) để tránh bug khi $excludeUserId = 0
        if ($excludeUserId !== null) {
            $query->where('UserId', '!=', $excludeUserId);
        }

        return $query->exists();
    }

    /**
     * ✅ Kiểm tra SĐT tồn tại (loại trừ user hiện tại)
     */
    public function checkPhoneExists($phone, $excludeUserId = null)
    {
        $query = User::where('PhoneNumber', $phone);

        // ✅ FIX: dùng !== null thay vì if(...)
        if ($excludeUserId !== null) {
            $query->where('UserId', '!=', $excludeUserId);
        }

        return $query->exists();
    }

    /**
     * Tạo mới
     */
    public function create(array $data)
    {
        $user = User::create($data);
        $this->clearCache();
        return $user;
    }

    /**
     * Cập nhật
     */
    public function update(User $user, array $data)
    {
        $user->update($data);
        $this->clearCache($user->UserId);
        return $user;
    }

    /**
     * Xóa
     */
    public function delete(User $user)
    {
        $id = $user->UserId;
        $result = $user->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Xóa Cache liên quan (dùng versioning, không loop)
     */
    public function clearCache($id = null)
    {
        Cache::forget('users_all');

        // Tăng version → toàn bộ key "users_paged_v*" cũ trở thành mồ côi (tự expire)
        Cache::increment('users_paged_version');

        if ($id) {
            Cache::forget("user_detail_{$id}");
        }
    }
}