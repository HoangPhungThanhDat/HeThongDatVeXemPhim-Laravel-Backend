<?php

namespace App\Repositories;

use App\Models\Payment;

class PaymentRepository
{
    /**
     * Lấy tất cả Payment
     */
    public function getAll()
    {
        return Payment::all();
    }

    /**
     * Tìm Payment theo ID
     */
    public function getById($id)
    {
        return Payment::findOrFail($id);
    }

    /**
     * Tạo mới Payment
     */
    public function create(array $data)
    {
        return Payment::create($data);
    }

    /**
     * Cập nhật Payment
     */
    public function update($id, array $data)
    {
        $Payment = Payment::findOrFail($id);
        $Payment->update($data);
        return $Payment;
    }

    /**
     * Xóa Payment
     */
    public function delete($id)
    {
        $payment = Payment::findOrFail($id);
        return $payment->delete();
    }
}
