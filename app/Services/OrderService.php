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

    public function getAll()
    {
        return $this->orderRepository->getAll();
    }

    public function getById($id)
    {
        return $this->orderRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->orderRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->orderRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->orderRepository->delete($id);
    }
}
