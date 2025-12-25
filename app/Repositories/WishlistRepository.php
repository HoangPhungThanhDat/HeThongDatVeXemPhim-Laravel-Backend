<?php

namespace App\Repositories;

use App\Models\Wishlist;

class WishlistRepository
{
    /**
     * Lấy tất cả Wishlist
     */
    public function getAll()
    {
        return Wishlist::all();
    }

    /**
     * Tìm Wishlist theo ID
     */
    public function getById($id)
    {
        return Wishlist::findOrFail($id);
    }

    /**
     * Tạo mới Wishlist
     */
    public function create(array $data)
    {
        return Wishlist::create($data);
    }

    /**
     * Cập nhật Wishlist
     */
    public function update($id, array $data)
    {
        $wishlist = Wishlist::findOrFail($id);
        $wishlist->update($data);
        return $wishlist;
    }

    /**
     * Xóa Wishlist
     */
    public function delete($id)
    {
        $wishlist = Wishlist::findOrFail($id);
        return $wishlist->delete();
    }
}
