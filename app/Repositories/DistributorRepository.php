<?php

namespace App\Repositories;

use App\Models\Distributor;
use Illuminate\Support\Facades\Cache;

class DistributorRepository
{
    const CACHE_TIME = 3600; // Cache 1 tiếng (Nhà phát hành ít thay đổi)

    /**
     * Lấy tất cả Distributor - ✅ ĐÃ CACHE
     */
    public function getAll()
    {
        return Cache::remember('distributors_all', self::CACHE_TIME, function () {
            return Distributor::orderBy('Name', 'asc')->get();
        });
    }

    /**
     * Tìm Distributor theo ID - ✅ ĐÃ CACHE
     */
    public function getById($id)
    {
        return Cache::remember("distributor_detail_{$id}", self::CACHE_TIME, function () use ($id) {
            return Distributor::findOrFail($id);
        });
    }

    /**
     * Tạo mới Distributor - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $distributor = Distributor::create($data);
        $this->clearCache();
        return $distributor;
    }

    /**
     * Cập nhật Distributor - ✅ XÓA CACHE
     */
    public function update($id, array $data)
    {
        $distributor = Distributor::findOrFail($id);
        $distributor->update($data);
        $this->clearCache($id);
        return $distributor;
    }

    /**
     * Xóa Distributor - ✅ XÓA CACHE
     */
    public function delete($id)
    {
        $distributor = Distributor::findOrFail($id);
        $result = $distributor->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Xóa Cache liên quan đến Distributor
     */
    public function clearCache($id = null)
    {
        Cache::forget('distributors_all');
        if ($id) {
            Cache::forget("distributor_detail_{$id}");
        }
    }
}