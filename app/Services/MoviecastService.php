<?php

namespace App\Services;

use App\Repositories\MoviecastRepository;
use Illuminate\Support\Facades\Auth;
class MoviecastService
{
    protected $repository;

    public function __construct(MoviecastRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAll()
    {
        return $this->repository->all();
    }

    public function find($CastId)
    {
        return $this->repository->find($CastId);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->repository->create($data);
    }

    public function update($CastId, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        $moviecast = $this->repository->find($CastId);
        if (!$moviecast) {
            return null;
        }
        return $this->repository->update($moviecast, $data);
    }

    public function delete($CastId)
    {
        $moviecast = $this->repository->find($CastId);
        if (!$moviecast) {
            return null;
        }
        return $this->repository->delete($moviecast);
    }
}
