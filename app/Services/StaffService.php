<?php

namespace App\Services;

use App\Repositories\StaffRepository;
use Illuminate\Support\Facades\Auth;
class StaffService
{
    protected $repository;

    public function __construct(StaffRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function find($StaffId)
    {
        return $this->repository->find($StaffId);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->repository->create($data);
    }

    public function update($StaffId, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        $staff = $this->repository->find($StaffId);
        if (!$staff) {
            return null;
        }
        return $this->repository->update($staff, $data);
    }

    public function delete($StaffId)
    {
        $staff = $this->repository->find($StaffId);
        if (!$staff) {
            return null;
        }
        return $this->repository->delete($staff);
    }
}
