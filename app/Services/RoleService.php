<?php

namespace App\Services;

use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\Auth;
class RoleService
{
    protected $repository;

    public function __construct(RoleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function find($RoleId)
    {
        return $this->repository->find($RoleId);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->repository->create($data);
    }

    public function update($RoleId, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        $role = $this->repository->find($RoleId);
        if (!$role) {
            return null;
        }
        return $this->repository->update($role, $data);
    }

    public function delete($RoleId)
    {
        $role = $this->repository->find($RoleId);
        if (!$role) {
            return null;
        }
        return $this->repository->delete($role);
    }
}
