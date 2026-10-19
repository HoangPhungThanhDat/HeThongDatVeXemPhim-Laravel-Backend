<?php

namespace App\Services;

use App\Repositories\OrderdetailRepository;
use Illuminate\Support\Facades\Auth;

class OrderdetailService
{
    protected $repository;

    public function __construct(OrderdetailRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function find($OrderDetailId)
    {
        return $this->repository->find($OrderDetailId);
    }

    public function create(array $data)
    {
        if (Auth::check()) {
            $data['CreatedBy'] = Auth::user()->UserId;
        }
        $data['CreatedAt'] = now();
        $data['UpdatedAt'] = now();
        return $this->repository->create($data);
    }

    public function update($OrderDetailId, array $data)
    {
        $orderDetail = $this->repository->find($OrderDetailId);
        if (!$orderDetail) {
            return null;
        }
        
        if (Auth::check()) {
            $data['UpdatedBy'] = Auth::user()->UserId;
        }
        $data['UpdatedAt'] = now();
        
        return $this->repository->update($orderDetail, $data);
    }

    public function delete($OrderDetailId)
    {
        $orderDetail = $this->repository->find($OrderDetailId);
        if (!$orderDetail) {
            return null;
        }
        return $this->repository->delete($orderDetail);
    }
}