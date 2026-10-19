<?php

namespace App\Services;

use App\Repositories\FoodanddrinkRepository;
use Illuminate\Support\Facades\Auth;

class FoodanddrinkService
{
    protected $repository;

    public function __construct(FoodanddrinkRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function find($ItemId)
    {
        return $this->repository->find($ItemId);
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

    public function update($ItemId, array $data)
    {
        $foodanddrink = $this->repository->find($ItemId);
        if (!$foodanddrink) {
            return null;
        }
        
        if (Auth::check()) {
            $data['UpdatedBy'] = Auth::user()->UserId;
        }
        $data['UpdatedAt'] = now();
        
        return $this->repository->update($foodanddrink, $data);
    }

    public function delete($ItemId)
    {
        $foodanddrink = $this->repository->find($ItemId);
        if (!$foodanddrink) {
            return null;
        }
        return $this->repository->delete($foodanddrink);
    }
}