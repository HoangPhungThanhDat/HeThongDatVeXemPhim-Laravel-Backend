<?php

namespace App\Repositories;

use App\Models\Orderdetail;
use Illuminate\Support\Facades\Cache;

class OrderdetailRepository
{
    // Cache ngắn hơn vì order details thay đổi liên tục
    const CACHE_TIME = 60; // 1 phút

    /**
     * Lấy tất cả - ✅ ĐÃ CACHE (kèm quan hệ nếu cần)
     */
    public function all()
    {
        return Cache::remember('orderdetails_all', self::CACHE_TIME, function () {
            return Orderdetail::with([
                'order',
                'ticket.showtime.movie',
                'ticket.seat',
                'foodanddrink'
            ])->orderBy('OrderDetailId', 'desc')->get();
        });
    }

    /**
     * Tìm theo ID - ✅ ĐÃ CACHE
     */
    public function find($OrderDetailId)
    {
        return Cache::remember("orderdetail_detail_{$OrderDetailId}", self::CACHE_TIME, function () use ($OrderDetailId) {
            return Orderdetail::with([
                'order',
                'ticket.showtime.movie',
                'ticket.seat',
                'foodanddrink'
            ])->find($OrderDetailId);
        });
    }

    /**
     * Tạo mới - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $orderdetail = Orderdetail::create($data);
        $this->clearCache();
        return $orderdetail;
    }

    /**
     * Cập nhật - ✅ XÓA CACHE
     */
    public function update(Orderdetail $orderdetail, array $data)
    {
        $orderdetail->update($data);
        $this->clearCache($orderdetail->OrderDetailId);
        return $orderdetail;
    }

    /**
     * Xóa - ✅ XÓA CACHE
     */
    public function delete(Orderdetail $orderdetail)
    {
        $id = $orderdetail->OrderDetailId;
        $result = $orderdetail->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Xóa Cache liên quan
     */
    public function clearCache($id = null)
    {
        Cache::forget('orderdetails_all');
        if ($id) {
            Cache::forget("orderdetail_detail_{$id}");
        }
    }
}