<?php

namespace App\Services;

use App\Repositories\OrderRepository;
use Illuminate\Support\Facades\Auth;

class OrderService
{
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getAllFormatted()
    {
        return $this->orderRepository->getAllFormatted();
    }

    public function getById($id)
    {
        return $this->orderRepository->getById($id);
    }

    public function create(array $data)
    {
        if (Auth::check()) {
            $data['CreatedBy'] = Auth::user()->UserId;
        }
        $data['CreatedAt'] = now();
        $data['UpdatedAt'] = now();
        return $this->orderRepository->create($data);
    }

    public function update($id, array $data)
    {
        if (Auth::check()) {
            $data['UpdatedBy'] = Auth::user()->UserId;
        }
        $data['UpdatedAt'] = now();
        return $this->orderRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->orderRepository->delete($id);
    }

    public function getOrderDetails($OrderId)
    {
        return $this->orderRepository->getOrderDetails($OrderId);
    }

    public function getMyOrders($userId, $limit = 10)
    {
        return $this->orderRepository->getMyOrders($userId, $limit);
    }

    public function checkOrderOwnership($orderId, $userId)
    {
        return $this->orderRepository->checkOrderOwnership($orderId, $userId);
    }

    public function getUserOrders($userId)
    {
        return $this->orderRepository->getUserOrders($userId);
    }
}