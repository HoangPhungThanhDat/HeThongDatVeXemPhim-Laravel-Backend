<?php

namespace App\Repositories;

use App\Models\Promotion;
use Illuminate\Support\Facades\Cache;

class PromotionRepository
{
    const CACHE_TIME = 1800; // Cache 30 phút (Khuyến mãi ít thay đổi)

    /**
     * Lấy tất cả - ✅ ĐÃ CACHE
     */
    public function all()
    {
        return Cache::remember('promotions_all', self::CACHE_TIME, function () {
            return Promotion::orderBy('CreatedAt', 'desc')->get();
        });
    }

    /**
     * ✅ Lấy dữ liệu phân trang (Chuyển câu query từ Controller xuống)
     */
    public function getPaged($page, $limit, $search = '', $status = '')
    {
        $cacheKey = "promotions_paged_p{$page}_l{$limit}_" . md5($search . $status);

        return Cache::remember($cacheKey, self::CACHE_TIME, function () use ($page, $limit, $search, $status) {
            $query = Promotion::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('Title', 'like', "%{$search}%")
                      ->orWhere('Code', 'like', "%{$search}%");
                });
            }

            if ($status) {
                $query->where('Status', $status);
            }

            $total      = $query->count();
            $totalPages = (int) ceil($total / $limit);
            $items      = $query->orderBy('PromotionId', 'desc')
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
     * Tìm theo ID - ✅ ĐÃ CACHE
     */
    public function find($PromotionId)
    {
        return Cache::remember("promotion_detail_{$PromotionId}", self::CACHE_TIME, function () use ($PromotionId) {
            return Promotion::find($PromotionId);
        });
    }

    /**
     * Tạo mới - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $promotion = Promotion::create($data);
        $this->clearCache();
        return $promotion;
    }

    /**
     * Cập nhật - ✅ XÓA CACHE
     */
    public function update(Promotion $promotion, array $data)
    {
        $promotion->update($data);
        $this->clearCache($promotion->PromotionId);
        return $promotion;
    }

    /**
     * Xóa - ✅ XÓA CACHE
     */
    public function delete(Promotion $promotion)
    {
        $id = $promotion->PromotionId;
        $result = $promotion->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Xóa Cache liên quan
     */
    public function clearCache($id = null)
    {
        Cache::forget('promotions_all');
        
        // Xóa cache phân trang (dọn dẹp các trang)
        for ($page = 1; $page <= 10; $page++) {
            for ($limit = 10; $limit <= 100; $limit += 10) {
                Cache::forget("promotions_paged_p{$page}_l{$limit}_" . md5('' . ''));
                Cache::forget("promotions_paged_p{$page}_l{$limit}_" . md5('' . 'Active'));
            }
        }

        if ($id) {
            Cache::forget("promotion_detail_{$id}");
        }
    }
}