<?php

namespace App\Repositories;

use App\Models\Order;

class OrderRepository
{
    /**
     * Lấy tất cả Order
     */
    public function getAll()
    {
        return Order::all();
    }

    /**
     * Tìm Order theo ID
     */
    public function getById($id)
    {
        return Order::findOrFail($id);
    }

    /**
     * Tạo mới Order
     */
    public function create(array $data)
    {
        return Order::create($data);
    }

    /**
     * Cập nhật Order
     */
    public function update($id, array $data)
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        return $order;
    }

    /**
     * Xóa order
     */
    public function delete($id)
    {
        $order = Order::findOrFail($id);
        return $order->delete();
    }
}
