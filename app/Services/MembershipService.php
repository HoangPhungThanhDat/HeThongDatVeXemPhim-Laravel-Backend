<?php

namespace App\Services;

use App\Repositories\MembershipRepository;
use Illuminate\Support\Facades\Auth;
class MembershipService
{
    protected $repository;

    public function __construct(MembershipRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function find($MembershipId)
    {
        return $this->repository->find($MembershipId);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->repository->create($data);
    }

    public function update($MembershipId, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        $membership = $this->repository->find($MembershipId);
        if (!$membership) {
            return null;
        }
        return $this->repository->update($membership, $data);
    }

    public function delete($MembershipId)
    {
        $membership = $this->repository->find($MembershipId);
        if (!$membership) {
            return null;
        }
        return $this->repository->delete($membership);
    }
}
