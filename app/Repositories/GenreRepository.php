<?php

namespace App\Repositories;

use App\Models\Genre;
use App\Models\Movie;
use Illuminate\Support\Facades\Cache;

class GenreRepository
{
    const CACHE_TIME = 300; // 5 phút

    /**
     * Lấy tất cả Genre - ✅ ĐÃ CACHE VÀ ĐẾM SỐ LƯỢNG PHIM
     */
    public function getAll()
    {
        return Cache::remember('genres_all_v2', self::CACHE_TIME, function () {
            // Lấy tất cả thể loại
            $genres = Genre::orderBy('Name')->get();

            // Duyệt qua từng thể loại để đếm số lượng phim (dựa vào cột GenreId trong bảng movies)
            $genres->map(function ($genre) {
                $genre->movieCount = Movie::where('GenreId', $genre->GenreId)->count();
                return $genre;
            });

            return $genres;
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
        // Xóa cache danh sách (đã đổi tên thành v2)
        Cache::forget('genres_all_v2');
        Cache::forget('genres_all'); // Xóa luôn cả cache cũ đề phòng
        
        // Nếu có ID cụ thể, xóa luôn cache chi tiết của thể loại đó
        if ($id) {
            Cache::forget("genre_detail_{$id}");
        }
    }
}