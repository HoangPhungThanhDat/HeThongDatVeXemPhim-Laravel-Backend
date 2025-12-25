<?php

namespace App\Repositories;

use App\Models\Distributor;

class DistributorRepository
{
    /**
     * Lấy tất cả Distributor
     */
    public function getAll()
    {
        return Distributor::all();
    }

    /**
     * Tìm Distributor theo ID
     */
    public function getById($id)
    {
        return Distributor::findOrFail($id);
    }

    /**
     * Tạo mới Distributor
     */
    public function create(array $data)
    {
        return Distributor::create($data);
    }

    /**
     * Cập nhật Distributor
     */
    public function update($id, array $data)
    {
        $distributor = Distributor::findOrFail($id);
        $distributor->update($data);
        return $distributor;
    }

    /**
     * Xóa distributor
     */
    public function delete($id)
    {
        $distributor = Distributor::findOrFail($id);
        return $distributor->delete();
    }
}
