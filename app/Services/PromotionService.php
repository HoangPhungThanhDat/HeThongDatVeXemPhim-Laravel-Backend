<?php

namespace App\Services;

use App\Repositories\PromotionRepository;
use Illuminate\Support\Facades\Auth;

class PromotionService
{
    protected $repository;

    public function __construct(PromotionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    /**
     * ✅ Hàm phân trang
     */
    public function getPaged($page, $limit, $search = '', $status = '')
    {
        return $this->repository->getPaged($page, $limit, $search, $status);
    }

    public function find($PromotionId)
    {
        return $this->repository->find($PromotionId);
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

    public function update($PromotionId, array $data)
    {
        $promotion = $this->repository->find($PromotionId);
        if (!$promotion) {
            return null;
        }
        
        if (Auth::check()) {
            $data['UpdatedBy'] = Auth::user()->UserId;
        }
        $data['UpdatedAt'] = now();
        
        return $this->repository->update($promotion, $data);
    }

    public function delete($PromotionId)
    {
        $promotion = $this->repository->find($PromotionId);
        if (!$promotion) {
            return null;
        }
        return $this->repository->delete($promotion);
    }
}