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

    public function find($PromotionId)
    {
        return $this->repository->find($PromotionId);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->repository->create($data);
    }

    public function update($PromotionId, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        $promotion = $this->repository->find($PromotionId);
        if (!$promotion) {
            return null;
        }
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
