<?php

namespace App\Repositories;

use App\Models\Genre;
use Illuminate\Support\Facades\Cache;

class GenreRepository
{
    const CACHE_TIME = 300; // 5 phút

    /**
     * Lấy tất cả Genre - ✅ ĐÃ CACHE
     */
    public function getAll()
    {
        return Cache::remember('genres_all', self::CACHE_TIME, function () {
            return Genre::orderBy('Name')->get();
        });
    }

    /**
     * Tìm Genre theo ID - ✅ ĐÃ CACHE
     */
    public function getById($id)
    {
        return Cache::remember("genre_detail_{$id}", self::CACHE_TIME, function () use ($id) {
            return Genre::findOrFail($id);
        });
    }

    /**
     * Tạo mới Genre - ✅ XÓA CACHE
     */
    public function create(array $data)
    {
        $genre = Genre::create($data);
        $this->clearCache();
        return $genre;
    }

    /**
     * Cập nhật Genre - ✅ XÓA CACHE
     */
    public function update($id, array $data)
    {
        $genre = Genre::findOrFail($id);
        $genre->update($data);
        $this->clearCache($id); // ✅ Truyền ID để xóa cache chi tiết
        return $genre;
    }

    /**
     * Xóa genre - ✅ XÓA CACHE
     */
    public function delete($id)
    {
        $genre = Genre::findOrFail($id);
        $result = $genre->delete();
        $this->clearCache($id); // ✅ Truyền ID để xóa cache chi tiết
        return $result;
    }

    /**
     * ✅ Xóa toàn bộ cache liên quan đến Genre
     */
    public function clearCache($id = null)
    {
        Cache::forget('genres_all');
        
        // Nếu có ID cụ thể, xóa luôn cache chi tiết của thể loại đó
        if ($id) {
            Cache::forget("genre_detail_{$id}");
        }
    }
}