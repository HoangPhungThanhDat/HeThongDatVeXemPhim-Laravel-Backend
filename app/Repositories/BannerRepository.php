<?php

namespace App\Repositories;

use App\Models\Banner;

class BannerRepository
{
    /**
     * Lấy tất cả banner
     */
    public function getAll()
    {
        return Banner::all();
    }

    /**
     * Tìm banner theo ID
     */
    public function getById($id)
    {
        return Banner::findOrFail($id);
    }

    /**
     * Tạo mới banner
     */
    public function create(array $data)
    {
        return Banner::create($data);
    }

    /**
     * Cập nhật banner
     */
    public function update($id, array $data)
    {
        $banner = Banner::findOrFail($id);
        $banner->update($data);
        return $banner;
    }

    /**
     * Xóa banner
     */
    public function delete($id)
    {
        $banner = Banner::findOrFail($id);
        return $banner->delete();
    }
}
