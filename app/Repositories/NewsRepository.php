<?php

namespace App\Repositories;

use App\Models\News;
use Illuminate\Support\Facades\Cache;

class NewsRepository
{
    const CACHE_TIME = 600; // Cache 10 phút (Tin tức thay đổi vừa phải)

    /**
     * Lấy tất cả News - ✅ ĐÃ CACHE
     */
    public function getAll()
    {
        return Cache::remember('news_all', self::CACHE_TIME, function () {
            return News::orderBy('CreatedAt', 'desc')->get();
        });
    }

    /**
     * Tìm News theo ID - ✅ ĐÃ CACHE
     */
    public function getById($id)
    {
        return Cache::remember("news_detail_{$id}", self::CACHE_TIME, function () use ($id) {
            return News::findOrFail($id);
        });
    }

    /**
     * Tạo mới News - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $news = News::create($data);
        $this->clearCache();
        return $news;
    }

    /**
     * Cập nhật News - ✅ XÓA CACHE
     */
    public function update($id, array $data)
    {
        $news = News::findOrFail($id);
        $news->update($data);
        $this->clearCache($id);
        return $news;
    }

    /**
     * Xóa News - ✅ XÓA CACHE
     */
    public function delete($id)
    {
        $news = News::findOrFail($id);
        $result = $news->delete();
        $this->clearCache($id);
        return $result;
    }

    /**
     * ✅ Xóa Cache liên quan
     */
    public function clearCache($id = null)
    {
        Cache::forget('news_all');
        if ($id) {
            Cache::forget("news_detail_{$id}");
        }
    }
}