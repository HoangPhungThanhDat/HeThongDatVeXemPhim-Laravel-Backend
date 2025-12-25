<?php

namespace App\Repositories;

use App\Models\News;

class NewsRepository
{
    /**
     * Lấy tất cả News
     */
    public function getAll()
    {
        return News::all();
    }

    /**
     * Tìm News theo ID
     */
    public function getById($id)
    {
        return News::findOrFail($id);
    }

    /**
     * Tạo mới News
     */
    public function create(array $data)
    {
        return News::create($data);
    }

    /**
     * Cập nhật News
     */
    public function update($id, array $data)
    {
        $news = News::findOrFail($id);
        $news->update($data);
        return $news;
    }

    /**
     * Xóa news
     */
    public function delete($id)
    {
        $news = News::findOrFail($id);
        return $news->delete();
    }
}
