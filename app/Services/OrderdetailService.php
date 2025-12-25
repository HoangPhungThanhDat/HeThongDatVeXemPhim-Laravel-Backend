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
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->repository->create($data);
    }

    public function update($OrderDetailId, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        $orderDetail = $this->repository->find($OrderDetailId);
        if (!$orderDetail) {
            return null;
        }
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
