<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Support\Facades\Cache;

class RoleRepository
{
    const CACHE_TIME = 1800; // 30 phút (Role ít thay đổi)

    public function all()
    {
        return Cache::remember('roles_all', self::CACHE_TIME, function () {
            return Role::orderBy('RoleName', 'asc')->get();
        });
    }

    public function find($RoleId)
    {
        return Cache::remember("role_detail_{$RoleId}", self::CACHE_TIME, function () use ($RoleId) {
            return Role::find($RoleId);
        });
    }

    public function create(array $data)
    {
        $role = Role::create($data);
        $this->clearCache();
        return $role;
    }

    public function update(Role $role, array $data)
    {
        $role->update($data);
        $this->clearCache($role->RoleId); // ✅ Truyền ID
        return $role;
    }

    public function delete(Role $role)
    {
        $id = $role->RoleId;
        $result = $role->delete();
        $this->clearCache($id); // ✅ Truyền ID
        return $result;
    }

    /**
     * ✅ Xóa Cache liên quan
     */
    public function clearCache($id = null)
    {
        Cache::forget('roles_all');
        
        // Nếu có ID cụ thể, xóa luôn cache chi tiết
        if ($id) {
            Cache::forget("role_detail_{$id}");
        }
    }
}