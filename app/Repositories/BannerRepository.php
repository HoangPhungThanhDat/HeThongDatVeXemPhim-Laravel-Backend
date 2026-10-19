<?php

namespace App\Repositories;

use App\Models\Banner;
use Illuminate\Support\Facades\Cache;

class BannerRepository
{
    const CACHE_TIME = 3600; // Cache 1 tiếng (Banner ít thay đổi)

    /**
     * Lấy tất cả banner - ✅ ĐÃ CACHE
     */
    public function getAll()
    {
        return Cache::remember('banners_all', self::CACHE_TIME, function () {
            // ✅ Trả về Collection của Model (không dùng toArray)
            return Banner::all(); 
        });
    }

    /**
     * Tìm banner theo ID - ✅ ĐÃ CACHE
     */
    public function getById($id)
    {
        return Cache::remember("banner_detail_{$id}", self::CACHE_TIME, function () use ($id) {
            return Banner::find($id); // Trả về null nếu không thấy, Service sẽ throw Exception
        });
    }

    /**
     * Tạo mới banner - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $banner = Banner::create($data);
        $this->clearCache();
        return $banner;
    }

    /**
     * Cập nhật banner - ✅ XÓA CACHE
     */
    public function update($id, array $data)
    {
        $banner = Banner::findOrFail($id);
        $banner->update($data);
        $this->clearCache($id); // ✅ Truyền ID để xóa cache chi tiết
        return $banner;
    }

    /**
     * Xóa banner - ✅ XÓA CACHE
     */
    public function delete($id)
    {
        $banner = Banner::findOrFail($id);
        $result = $banner->delete();
        $this->clearCache($id); // ✅ Truyền ID để xóa cache chi tiết
        return $result;
    }

    /**
     * ✅ Xóa toàn bộ cache liên quan đến Banner
     */
    public function clearCache($id = null)
    {
        Cache::forget('banners_all');
        
        // Nếu có ID cụ thể, xóa cache chi tiết của banner đó
        if ($id) {
            Cache::forget("banner_detail_{$id}");
        }
    }
}