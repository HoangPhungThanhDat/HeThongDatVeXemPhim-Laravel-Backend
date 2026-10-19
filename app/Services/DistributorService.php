<?php

namespace App\Services;

use App\Repositories\DistributorRepository;
use Illuminate\Support\Facades\Auth;

class DistributorService
{
    protected $distributorRepository;

    public function __construct(DistributorRepository $distributorRepository)
    {
        $this->distributorRepository = $distributorRepository;
    }

    public function getAll()
    {
        return $this->distributorRepository->getAll();
    }

    public function getById($id)
    {
        return $this->distributorRepository->getById($id);
    }

    public function create(array $data)
    {
        if (Auth::check()) {
            $data['CreatedBy'] = Auth::user()->UserId;
        }
        $data['CreatedAt'] = now();
        $data['UpdatedAt'] = now();
        return $this->distributorRepository->create($data);
    }

    public function update($id, array $data)
    {
        if (Auth::check()) {
            $data['UpdatedBy'] = Auth::user()->UserId;
        }
        $data['UpdatedAt'] = now();
        return $this->distributorRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->distributorRepository->delete($id);
    }
}