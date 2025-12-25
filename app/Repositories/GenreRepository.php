<?php

namespace App\Repositories;

use App\Models\Genre;

class GenreRepository
{
    /**
     * Lấy tất cả Genre
     */
    public function getAll()
    {
        return Genre::all();
    }

    /**
     * Tìm Genre theo ID
     */
    public function getById($id)
    {
        return Genre::findOrFail($id);
    }

    /**
     * Tạo mới Genre
     */
    public function create(array $data)
    {
        return Genre::create($data);
    }

    /**
     * Cập nhật Genre
     */
    public function update($id, array $data)
    {
        $genre = Genre::findOrFail($id);
        $genre->update($data);
        return $genre;
    }

    /**
     * Xóa genre
     */
    public function delete($id)
    {
        $genre = Genre::findOrFail($id);
        return $genre->delete();
    }
}
