<?php

namespace App\Repositories;

use App\Models\Foodanddrink;
use Illuminate\Support\Facades\Cache;

class FoodanddrinkRepository
{
    const CACHE_TIME = 3600; // Cache 1 tiếng (Đồ ăn uống ít thay đổi)

    /**
     * Lấy tất cả Foodanddrink - ✅ ĐÃ CACHE
     */
    public function all()
    {
        return Cache::remember('foodanddrinks_all', self::CACHE_TIME, function () {
            return Foodanddrink::orderBy('Name', 'asc')->get();
        });
    }

    /**
     * Tìm Foodanddrink theo ID - ✅ ĐÃ CACHE
     */
    public function find($ItemId)
    {
        return Cache::remember("foodanddrink_detail_{$ItemId}", self::CACHE_TIME, function () use ($ItemId) {
            return Foodanddrink::find($ItemId);
        });
    }

    /**
     * Tạo mới - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $foodanddrink = Foodanddrink::create($data);
        $this->clearCache();
        return $foodanddrink;
    }

    /**
     * Cập nhật - ✅ XÓA CACHE
     */
    public function update(Foodanddrink $foodanddrink, array $data)
    {
        $foodanddrink->update($data);
        $this->clearCache($foodanddrink->ItemId);
        return $foodanddrink;
    }

    /**
     * Xóa - ✅ XÓA CACHE
     */
    public function delete(Foodanddrink $foodanddrink)
    {
        $id = $foodanddrink->ItemId;
        $result = $foodanddrink->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Xóa Cache liên quan
     */
    public function clearCache($id = null)
    {
        Cache::forget('foodanddrinks_all');
        if ($id) {
            Cache::forget("foodanddrink_detail_{$id}");
        }
    }
}